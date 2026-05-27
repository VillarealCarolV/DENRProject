<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user with filtering
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications();
        
        // Filter by date
        if ($request->has('filter_date') && !empty($request->filter_date)) {
            $query->whereDate('created_at', $request->filter_date);
        }
        
        // Filter by status
        if ($request->has('filter_status') && !empty($request->filter_status)) {
            $query->where('data->status', $request->filter_status);
        }
        
        // Filter by tracking number
        if ($request->has('filter_tracking') && !empty($request->filter_tracking)) {
            $query->where('data->tracking_no', 'LIKE', '%' . $request->filter_tracking . '%');
        }
        
        $notifications = $query->latest()->paginate(15);
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get unread notification count (AJAX endpoint)
     * Used to update the bell icon count without page reload
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();
        $notifications = Auth::user()->unreadNotifications()->latest()->take(5)->get();
        
        return response()->json([
            'count' => $count,
            'notifications' => $notifications->map(fn($notif) => [
                'id' => $notif->id,
                'tracking_no' => $notif->data['tracking_no'] ?? 'N/A',
                'message' => $notif->data['message'] ?? 'New Notification',
                'status' => $notif->data['status'] ?? 'pending',
                'created_at' => $notif->created_at->diffForHumans(),
                'created_at_full' => $notif->created_at->format('M d, Y g:i A'),
            ])
        ]);
    }

    /**
     * Mark notification as read via AJAX
     */
    public function markAsReadAjax(Request $request, $id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'count' => Auth::user()->unreadNotifications()->count(),
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
