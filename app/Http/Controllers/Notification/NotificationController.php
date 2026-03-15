<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification(Request $request): JsonResponse
    {
        // Placeholder implementation to keep the API route functional.
        return response()->json([
            'success' => true,
            'message' => 'Notification endpoint is available.',
            'payload' => $request->all(),
        ]);
    }
}
