<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notificação marcada como lida.');
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    public function destroy($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return back()->with('success', 'Notificação excluída com sucesso.');
    }
} 