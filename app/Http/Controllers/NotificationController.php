<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read and redirect to its URL.
     */
    public function readAndRedirect(Request $request, string $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $notification = $user->notifications()->find($id);
        if (!$notification) {
            return redirect()->route('home')->with('error', 'Notification not found.');
        }

        $notification->markAsRead();
        $data = $notification->data;
        $url = $data['url'] ?? route('home');

        return redirect($url);
    }
}
