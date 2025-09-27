<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatSession;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private chat channels for your chat functionality
Broadcast::channel('private-chat.{sessionId}', function ($user, $sessionId) {
    return ChatSession::where('id', $sessionId)
        ->where(function ($query) use ($user) {
            $query->where('user_one_id', $user->id)
                  ->orWhere('user_two_id', $user->id);
        })->exists();
});

// Alternative channel naming for direct Ably integration
Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    return ChatSession::where('id', $sessionId)
        ->where(function ($query) use ($user) {
            $query->where('user_one_id', $user->id)
                  ->orWhere('user_two_id', $user->id);
        })->exists();
});