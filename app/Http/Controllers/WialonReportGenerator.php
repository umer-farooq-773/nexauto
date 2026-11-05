<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\WialonReportTemplate;
use App\Models\WialonUnitGroup;
use App\Services\WialonReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

/**
 * WialonReportGenerator
 */
class WialonReportGenerator extends Controller
{
    public function __construct(protected WialonReportService $wialonReportService)
    {
    }

    /**
     * GET reports-analytics
     * Renders the report search page with active templates, vehicles, and unit groups.
     */
    public function index()
    {
        // 1. Get database vehicles that have a valid gps_unit_id
        $vehicles = Vehicle::whereNotNull('gps_unit_id')
            ->where('gps_unit_id', '!=', '')
            ->where('status', '!=', '0')
            ->orderBy('fleet_no')
            ->get(['id', 'fleet_no', 'truck_no', 'gps_unit_id'])
            ->map(fn (Vehicle $v) => [
                'id'           => (int) $v->gps_unit_id,
                'fleet_no'     => $v->fleet_no,
                'truck_no'     => $v->truck_no,
                'display_name' => $v->fleet_no . ($v->truck_no ? " ({$v->truck_no})" : ''),
            ])->values();

        // 2. Get active unit groups
        $groups = WialonUnitGroup::active()
            ->orderBy('name')
            ->get(['id', 'wialon_group_id', 'name', 'units_count'])
            ->map(fn (WialonUnitGroup $g) => [
                'id'           => (int) $g->wialon_group_id,
                'name'         => $g->name,
                'units_count'  => (int) $g->units_count,
                'display_name' => $g->name . " ({$g->units_count} units)",
            ])->values();

        // 3. Get active report templates
        $templates = WialonReportTemplate::active()
            ->orderBy('name')
            ->get()
            ->map(fn (WialonReportTemplate $t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'resourceId'   => (int) $t->wialon_resource_id,
                'templateId'   => (int) $t->wialon_template_id,
                'templateType' => $t->type,
                'objectType'   => $t->normalized_type,
            ])->values();

        $initialData = [
            'templates' => $templates,
            'vehicles'  => $vehicles,
            'groups'    => $groups,
        ];

        return view('auth.pages.reports.index', [
            'vehicles'    => $vehicles,
            'groups'      => $groups,
            'templates'   => $templates,
            'initialData' => $initialData,
        ]);
    }

    /**
     * POST reports-analytics/sync
     * Master Wialon synchronization operation: reconciles Templates, Vehicles, and Unit Groups.
     */
    public function sync()
    {
        try {
            $result = $this->wialonReportService->syncWialon();

            return response()->json($result);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Wialon sync connection timeout', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Connection timeout. Please check your network connection and try again.',
            ], 504);
        } catch (\RuntimeException $e) {
            Log::error('Wialon sync runtime error', ['message' => $e->getMessage()]);

            $message = $e->getMessage();
            if (str_contains($message, 'session') || str_contains($message, 'Invalid session')) {
                $message = 'Session expired. Please try again.';
            } elseif (str_contains($message, 'Access denied')) {
                $message = 'Access denied. Please check your Wialon permissions.';
            } else {
                $message = 'Could not sync with Wialon: ' . $e->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Unexpected error during Wialon sync', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during synchronization. Please try again.',
            ], 500);
        }
    }

    /**
     * Backward-compatible alias for template sync route
     */
    public function syncTemplates()
    {
        return $this->sync();
    }

    /**
     * POST reports-analytics/generate
     * Body: { template_id, report_object_id (or wialon_unit_id), from ("Y-m-d H:i"), to ("Y-m-d H:i") }
     *
     * Returns the report as table rows + chart series + map points ready for frontend rendering.
     */
    public function generate(Request $request)
    {
        // Support both report_object_id and legacy wialon_unit_id
        $objectId = $request->input('report_object_id') ?? $request->input('wialon_unit_id');

        if ($objectId === null || $objectId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid report object.',
            ], 422);
        }

        $request->merge(['report_object_id' => (int) $objectId]);

        try {
            $validated = $request->validate([
                'report_object_id' => ['required', 'integer'],
                'template_id'      => ['required', 'uuid', 'exists:wialon_report_templates,id'],
                'from'             => ['required', 'date'],
                'to'               => ['required', 'date', 'after_or_equal:from'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input: ' . implode(', ', $e->validator->errors()->all()),
                'errors'  => $e->validator->errors(),
            ], 422);
        }

        $template = WialonReportTemplate::find($validated['template_id']);

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Report template not found. Please sync Wialon and try again.',
            ], 404);
        }

        $from = Carbon::parse($validated['from'])->timestamp;
        $to   = Carbon::parse($validated['to'])->timestamp;

        // Validate time range - up to 3 months (93 days)
        $daysDiff = ($to - $from) / 86400;
        if ($daysDiff > 93) {
            return response()->json([
                'success' => false,
                'message' => 'Time range too large. Please select a period of up to 3 months (93 days). For larger periods, generate separate monthly reports.',
            ], 422);
        }

        $warningMessage = null;
        if ($daysDiff > 31) {
            $warningMessage = 'Large date range detected. This may take longer to process.';
        }

        try {
            Log::info('Generating Wialon report', [
                'template_name' => $template->name,
                'template_type' => $template->type,
                'object_id'     => $validated['report_object_id'],
                'from'          => Carbon::createFromTimestamp($from)->toDateTimeString(),
                'to'            => Carbon::createFromTimestamp($to)->toDateTimeString(),
            ]);

            $execResult = $this->wialonReportService->executeReport(
                resourceId: $template->wialon_resource_id,
                templateId: $template->wialon_template_id,
                objectId: (int) $validated['report_object_id'],
                fromTimestamp: $from,
                toTimestamp: $to,
            );

            if (empty($execResult) || !isset($execResult['reportResult'])) {
                throw new \RuntimeException('Wialon returned empty or invalid report result');
            }

            $tables = $execResult['reportResult']['tables'] ?? [];

            if (empty($tables)) {
                Log::warning('Wialon report returned no tables', [
                    'template'  => $template->name,
                    'object_id' => $validated['report_object_id'],
                ]);

                $this->wialonReportService->cleanupReport();

                return response()->json([
                    'success' => true,
                    'result'  => [
                        'tables' => [],
                        'charts' => [],
                        'map'    => [],
                    ],
                    'message' => 'No data found for the selected time period.',
                ]);
            }

            $result = [
                'tables'  => [],
                'charts'  => [],
                'map'     => [],
                'summary' => [
                    'template_name' => $template->name,
                    'template_type' => $template->normalized_type,
                    'object_id'     => $validated['report_object_id'],
                    'period_from'   => Carbon::createFromTimestamp($from)->format('Y-m-d H:i'),
                    'period_to'     => Carbon::createFromTimestamp($to)->format('Y-m-d H:i'),
                    'total_tables'  => count($tables),
                ],
            ];

            foreach ($tables as $index => $table) {
                $rowCount = (int) ($table['rows'] ?? 0);

                if ($rowCount > 0) {
                    try {
                        $rows = $this->wialonReportService->getReportRows(
                            tableIndex: $index,
                            indexFrom: 0,
                            indexTo: min($rowCount - 1, 999), // Limit to 1000 rows max
                        );

                        $result['tables'][] = [
                            'name'     => $table['name'] ?? "table_{$index}",
                            'label'    => $table['label'] ?? $table['name'] ?? "Table " . ($index + 1),
                            'headers'  => $table['header'] ?? [],
                            'rows'     => $rows,
                            'rowCount' => $rowCount,
                        ];
                    } catch (\Throwable $e) {
                        Log::error('Failed to fetch table rows', [
                            'table_index' => $index,
                            'error'       => $e->getMessage(),
                        ]);
                    }
                }

                // Templates whose table type indicates a chart
                if (!empty($table['chart']) || str_contains(strtolower($table['name'] ?? ''), 'chart')) {
                    try {
                        $chartData = $this->wialonReportService->getReportChart($index, $from, $to);

                        if (!empty($chartData)) {
                            $result['charts'][] = [
                                'tableIndex' => $index,
                                'label'      => $table['label'] ?? $table['name'] ?? "Chart " . ($index + 1),
                                'data'       => $chartData,
                            ];
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to fetch chart data', [
                            'table_index' => $index,
                            'error'       => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Map points: fetch route coordinates for unit reports, or use report map data
            if (!empty($tables)) {
                try {
                    if ($template->normalized_type === 'unit') {
                        $result['map'] = $this->wialonReportService->getUnitRouteCoordinates(
                            unitId: (int) $validated['report_object_id'],
                            fromTimestamp: $from,
                            toTimestamp: $to
                        );
                    } else {
                        // For group reports, fetch map data if available from the report tables
                        $mapData = [];
                        foreach ($tables as $idx => $t) {
                            $m = $this->wialonReportService->getReportMap($idx, 0, 500);
                            if (!empty($m)) {
                                $mapData = $m;
                                break;
                            }
                        }
                        $result['map'] = $mapData;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to fetch map coordinates', [
                        'error' => $e->getMessage(),
                    ]);
                    $result['map'] = [];
                }
            }

            $this->wialonReportService->cleanupReport();

            Log::info('Wialon report generated successfully', [
                'template'     => $template->name,
                'tables_count' => count($result['tables']),
                'charts_count' => count($result['charts']),
                'map_points'   => count($result['map']),
            ]);

            $response = ['success' => true, 'result' => $result];

            if ($warningMessage) {
                $response['warning'] = $warningMessage;
            }

            return response()->json($response);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->wialonReportService->cleanupReport();

            Log::error('Wialon connection timeout', [
                'message'  => $e->getMessage(),
                'template' => $template->name,
            ]);

            return response()->json([
                'success'    => false,
                'message'    => 'Request timed out. The selected time range may be too large. Please try a shorter period.',
                'error_type' => 'timeout',
            ], 504);

        } catch (\RuntimeException $e) {
            $this->wialonReportService->cleanupReport();

            Log::error('Wialon report generation error', [
                'message'  => $e->getMessage(),
                'template' => $template->name ?? 'unknown',
            ]);

            $message = $e->getMessage();
            if (str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
                $message = 'Request timed out. Please try a shorter time period.';
            } elseif (str_contains($message, 'session') || str_contains($message, 'Invalid session')) {
                $message = 'Session expired. Please try again.';
            } elseif (str_contains($message, 'No messages for selected interval')) {
                $message = 'No data available for the selected time period.';
            } elseif (str_contains($message, 'Access denied')) {
                $message = 'Access denied. Please check your permissions.';
            }

            return response()->json([
                'success'    => false,
                'message'    => $message,
                'error_type' => 'runtime_error',
            ], 500);

        } catch (\Throwable $e) {
            $this->wialonReportService->cleanupReport();

            Log::error('Unexpected error during report generation', [
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
                'template' => $template->name ?? 'unknown',
            ]);

            return response()->json([
                'success'    => false,
                'message'    => 'An unexpected error occurred. Please try again or contact support if the problem persists.',
                'error_type' => 'unexpected_error',
            ], 500);
        }
    }

    /**
     * POST reports-analytics/export
     * Same params as generate(), plus "format": "xlsx" | "pdf" | "csv" | "html" | "xml"
     */
    public function export(Request $request)
    {
        $objectId = $request->input('report_object_id') ?? $request->input('wialon_unit_id');

        if ($objectId === null || $objectId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid report object before exporting.',
            ], 422);
        }

        $request->merge(['report_object_id' => (int) $objectId]);

        $validated = $request->validate([
            'report_object_id' => ['required', 'integer'],
            'template_id'      => ['required', 'uuid', 'exists:wialon_report_templates,id'],
            'from'             => ['required', 'date'],
            'to'               => ['required', 'date', 'after_or_equal:from'],
            'format'           => ['required', 'in:xlsx,pdf,csv,html,xml'],
        ]);

        $template = WialonReportTemplate::findOrFail($validated['template_id']);

        $formatMap = [
            'html' => WialonReportService::EXPORT_FORMAT_HTML,
            'pdf'  => WialonReportService::EXPORT_FORMAT_PDF,
            'xlsx' => WialonReportService::EXPORT_FORMAT_XLSX,
            'xml'  => WialonReportService::EXPORT_FORMAT_XML,
            'csv'  => WialonReportService::EXPORT_FORMAT_CSV,
        ];

        $extensionMap = [
            'html' => 'html', 'pdf' => 'pdf', 'xlsx' => 'xlsx', 'xml' => 'xml', 'csv' => 'csv',
        ];

        try {
            $this->wialonReportService->executeReport(
                resourceId: $template->wialon_resource_id,
                templateId: $template->wialon_template_id,
                objectId: (int) $validated['report_object_id'],
                fromTimestamp: Carbon::parse($validated['from'])->timestamp,
                toTimestamp: Carbon::parse($validated['to'])->timestamp,
            );

            $fileName = str($template->name)->slug() . '-' . now()->format('Ymd_His');

            $file = $this->wialonReportService->exportReport(
                format: $formatMap[$validated['format']],
                outputFileName: $fileName,
            );

            $this->wialonReportService->cleanupReport();

            return response($file['contents'], 200, [
                'Content-Type'        => $file['contentType'],
                'Content-Disposition' => 'attachment; filename="' . $fileName . '.' . $extensionMap[$validated['format']] . '"',
            ]);

        } catch (\Throwable $e) {
            Log::error('Wialon report export failed', ['message' => $e->getMessage()]);

            $this->wialonReportService->cleanupReport();

            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
