<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của user hiện tại
     */
    public function index(Request $request)
    {
        // Lấy 50 thông báo gần nhất
        $notifications = $request->user()->notifications()->take(50)->get();
        return response()->json($notifications);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['message' => 'Đã đánh dấu đọc']);
    }

    /**
     * Đánh dấu TẤT CẢ thông báo là đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        
        return response()->json(['message' => 'Đã đánh dấu đọc tất cả']);
    }
}
