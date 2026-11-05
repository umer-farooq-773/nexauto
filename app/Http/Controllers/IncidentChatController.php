<?php

namespace App\Http\Controllers;

use App\Models\IncidentChat;
use App\Models\User;
use App\Constants\Constants;
use App\Notifications\IncidentChatNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IncidentChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Allowed incident types — must match the route slugs / model names.
     */
    private const ALLOWED_TYPES = [
        'impoundment',
        'vehicle_bogged_down',
        'vehicle_breakdown',
        'illegal_detention',
        'illegal_haulage',
        'illegal_shortage',
        'breathaliser_test',
        'accident',
        'route_diversion',
        'driver_misconduct',
        'goods_inspection',
        'vehicle_maintenance',
    ];

    /**
     * Roles that are allowed to participate in incident chat.
     */
    private const ALLOWED_ROLES = [
        Constants::ADMIN,
        Constants::SUPERADMIN,
        Constants::CHAMPS_MANAGEMENT,
        Constants::CLIENT_MANAGEMENT,
        Constants::CLIENT_DIRECTORS,
    ];

    /**
     * GET  /incident-chat/{type}/{id}
     * Fetch all messages for an incident (JSON).
     */
    public function fetch(Request $request, string $incidentType, string $incidentId)
    {
        if (!in_array($incidentType, self::ALLOWED_TYPES, true)) {
            return response()->json(['error' => 'Invalid incident type.'], 404);
        }

        $user = Auth::user();
        if (!$user || !$user->hasRole(self::ALLOWED_ROLES)) {
            return response()->json(['error' => 'Unauthorised.'], 403);
        }

        $chats = IncidentChat::with('user')
            ->forIncident($incidentType, $incidentId)
            ->oldest()
            ->get()
            ->map(fn ($chat) => $this->formatMessage($chat));

        return response()->json(['chats' => $chats]);
    }

    /**
     * POST  /incident-chat/{type}/{id}
     * Send a new chat message for an incident.
     */
    public function send(Request $request, string $incidentType, string $incidentId)
    {
        if (!in_array($incidentType, self::ALLOWED_TYPES, true)) {
            return response()->json(['error' => 'Invalid incident type.'], 404);
        }

        $user = Auth::user();
        if (!$user || !$user->hasRole(self::ALLOWED_ROLES)) {
            return response()->json(['error' => 'Unauthorised.'], 403);
        }

        $validated = $request->validate([
            'message'    => ['required_without:attachment', 'nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file           = $request->file('attachment');
            $filename       = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('incident-chat-attachments', $filename, 'public');
        }

        // Generate incident URL for redirect
        $incidentLabel = $this->getIncidentLabel($incidentType);
        $targetUrl = $this->getIncidentUrl($incidentType, $incidentId);

        $chat = IncidentChat::create([
            'incident_id'   => $incidentId,
            'incident_type' => $incidentType,
            'user_id'       => $user->id,
            'role_type'     => $this->resolveRole($user),
            'message'       => $validated['message'] ?? '',
            'attachment'    => $attachmentPath,
            'incident_url'  => $targetUrl,
            'read_by'       => [(string) $user->id], // Mark as read by sender
        ]);

        $chat->load('user');

        // Send notifications to other users
        $this->sendChatNotifications($chat, $incidentType, $incidentId, $incidentLabel, $targetUrl, $user);

        return response()->json([
            'success' => true,
            'chat'    => $this->formatMessage($chat),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resolveRole($user): string
    {
        $priority = [
            Constants::SUPERADMIN,
            Constants::ADMIN,
            Constants::CHAMPS_MANAGEMENT,
            Constants::CLIENT_DIRECTORS,
            Constants::CLIENT_MANAGEMENT,
        ];

        foreach ($priority as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return $user->getRoleNames()->first() ?? 'user';
    }

    private function formatMessage(IncidentChat $chat): array
    {
        return [
            'id'         => $chat->id,
            'user_id'    => $chat->user_id,
            'user_name'  => $chat->user?->full_name ?? $chat->user?->name ?? 'Unknown',
            'role_type'  => $chat->role_type,
            'role_label' => $this->roleLabel($chat->role_type),
            'message'    => $chat->message,
            'attachment' => $chat->attachment ? Storage::url($chat->attachment) : null,
            'is_mine'    => $chat->user_id === Auth::id(),
            'created_at' => $chat->created_at?->format('d M Y, h:i A'),
        ];
    }

    private function roleLabel(?string $role): string
    {
        return match ($role) {
            Constants::SUPERADMIN        => 'Super Admin',
            Constants::ADMIN             => 'Admin',
            Constants::CHAMPS_MANAGEMENT => 'Champs Management',
            Constants::CLIENT_DIRECTORS  => 'Client Director',
            Constants::CLIENT_MANAGEMENT => 'Client Management',
            default                      => ucwords(str_replace('_', ' ', $role ?? 'User')),
        };
    }

    private function getIncidentLabel(string $incidentType): string
    {
        return match ($incidentType) {
            'impoundment' => 'Impoundment',
            'vehicle_bogged_down' => 'Vehicle Bogged Down',
            'vehicle_breakdown' => 'Vehicle Breakdown',
            'illegal_detention' => 'Illegal Detention',
            'illegal_haulage' => 'Illegal Haulage',
            'illegal_shortage' => 'Illegal Shortage',
            'breathaliser_test' => 'Breathaliser Test',
            'accident' => 'Accident',
            'route_diversion' => 'Route Diversion',
            'driver_misconduct' => 'Driver Misconduct',
            'goods_inspection' => 'Goods Inspection',
            'vehicle_maintenance' => 'Vehicle Maintenance',
            default => ucwords(str_replace('_', ' ', $incidentType)),
        };
    }

    private function getIncidentUrl(string $incidentType, string $incidentId): string
    {
        $routeName = match ($incidentType) {
            'impoundment' => 'impoundment.show',
            'vehicle_bogged_down' => 'vehicle_bogged_down.show',
            'vehicle_breakdown' => 'vehicle_breakdown.show',
            'illegal_detention' => 'illegal_detention.show',
            'illegal_haulage' => 'illegal_haulage.show',
            'illegal_shortage' => 'illegal_shortage.show',
            'breathaliser_test' => 'breathaliser_test.show',
            'accident' => 'accident.show',
            'route_diversion' => 'route_diversion.show',
            'driver_misconduct' => 'driver_misconduct.show',
            'goods_inspection' => 'goods_inspection.show',
            'vehicle_maintenance' => 'maintenance.show',
            default => null,
        };

        if ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
            return route($routeName, $incidentId);
        }

        // Fallback for dash-style route names if defined
        $dashRoute = str_replace('_', '-', $incidentType) . '.show';
        if (\Illuminate\Support\Facades\Route::has($dashRoute)) {
            return route($dashRoute, $incidentId);
        }

        return '#';
    }

    private function sendChatNotifications(IncidentChat $chat, string $incidentType, string $incidentId, string $incidentLabel, string $targetUrl, $sender): void
    {
        try {
            $notification = new IncidentChatNotification(
                $incidentType,
                $incidentId,
                $incidentLabel,
                $sender->full_name ?? $sender->name ?? 'Unknown',
                $chat->message,
                $targetUrl
            );

            // Get all users who should receive notifications (excluding sender)
            $recipients = User::role(self::ALLOWED_ROLES)
                ->where('id', '!=', $sender->id)
                ->get()
                ->unique('id');

            foreach ($recipients as $recipient) {
                try {
                    $recipient->notifyNow($notification, ['database']);
                } catch (\Throwable $e) {
                    Log::warning("Incident chat notification failed", [
                        'user_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to send incident chat notifications", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
