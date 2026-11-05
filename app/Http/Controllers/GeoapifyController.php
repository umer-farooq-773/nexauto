<?php

namespace App\Http\Controllers;

use App\Services\GeoapifyService;
use Illuminate\Http\Request;

class GeoapifyController extends Controller
{
    public function __construct(protected GeoapifyService $geoapifyService)
    {
    }

    public function autocomplete(Request $request)
    {
        $text = (string) $request->get('text', '');
        $results = $this->geoapifyService->autocomplete($text);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function routing(Request $request)
    {
        $waypoints = $request->get('waypoints', []);

        if (is_string($waypoints)) {
            $waypoints = json_decode($waypoints, true) ?? [];
        }

        if (!is_array($waypoints) || count($waypoints) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid waypoints payload.',
            ], 422);
        }

        $route = $this->geoapifyService->calculateRoute($waypoints);

        return response()->json($route);
    }
}
