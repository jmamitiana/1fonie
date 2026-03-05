<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, Mission $mission)
    {
        $user = $request->user();

        // Check if user is authorized to view messages
        $isAuthorized = false;
        
        if ($user->role === 'company' && $mission->company_id === $user->company->id) {
            $isAuthorized = true;
        } elseif ($user->role === 'provider' && $mission->provider_id === $user->provider->id) {
            $isAuthorized = true;
        } elseif ($user->role === 'admin') {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $mission->messages()
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, Mission $mission)
    {
        $user = $request->user();

        $request->validate([
            'content' => 'required|string',
        ]);

        // Determine receiver based on user role
        if ($user->role === 'company') {
            if ($mission->company_id !== $user->company->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            if (!$mission->provider_id) {
                return response()->json(['message' => 'No provider assigned to this mission'], 400);
            }
            $receiverId = $mission->provider->user_id;
        } elseif ($user->role === 'provider') {
            if ($mission->provider_id !== $user->provider->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            $receiverId = $mission->company->user_id;
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'mission_id' => $mission->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'content' => $request->content,
        ]);

        // Send notification
        Notification::createForUser(
            $receiverId,
            'new_message',
            'New Message',
            "You have a new message regarding '{$mission->title}'",
            ['mission_id' => $mission->id, 'message_id' => $message->id]
        );

        return response()->json([
            'message' => $message->load(['sender', 'receiver']),
        ], 201);
    }

    public function markAsRead(Request $request, Message $message)
    {
        $user = $request->user();

        if ($message->receiver_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->markAsRead();

        return response()->json([
            'message' => $message,
        ]);
    }
}
