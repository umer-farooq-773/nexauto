<?php

namespace App\Http\Controllers;

use App\Models\IncidentChat;
use App\Constants\Constants;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatNotificationController extends Controller
{
    /**
     * Return the total number of unread messages for the authenticated user.
     * An unread message is one where to_id = auth user AND seen = 0.
     */
    public function unreadCount(): JsonResponse
    {
        $count = DB::table('ch_messages')
            ->where('to_id', Auth::id())
            ->where('seen', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Return the total number of unread incident chat messages for the authenticated user.
     * An unread message is one where the user_id is not the auth user AND the user's ID is not in read_by.
     */
    public function incidentChatUnreadCount(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        // Count incident chat messages where:
        // - The message was not sent by the current user
        // - The current user's ID is not in the read_by array
        $count = IncidentChat::where('user_id', '!=', $user->id)
            ->where(function ($query) use ($user) {
                $query->whereNull('read_by')
                      ->orWhereJsonDoesntContain('read_by', (string) $user->id);
            })
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark incident chat messages as read for a specific incident.
     */
    public function markIncidentChatAsRead(string $incidentType, string $incidentId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        IncidentChat::forIncident($incidentType, $incidentId)
            ->where('user_id', '!=', $user->id)
            ->get()
            ->each(function ($chat) use ($user) {
                $readBy = $chat->read_by ?? [];
                if (!in_array((string) $user->id, $readBy)) {
                    $readBy[] = (string) $user->id;
                    $chat->read_by = $readBy;
                    $chat->save();
                }
            });

        return response()->json(['success' => true]);
    }
}
