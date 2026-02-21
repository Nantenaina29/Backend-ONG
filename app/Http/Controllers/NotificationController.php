<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied. Admins only.'], 403);
        }

        $notifications = Notification::with('user') 
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Manamarika notification iray ho voavaky
     */
    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['is_read' => true]); // Ataovy azo antoka fa misy 'is_read' ny tabilao

            return response()->json(['message' => 'Notification marquée comme lue.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mamafa notification iray
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return response()->json(['message' => 'Notification supprimée avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mamafa ny notification REHETRA (Clean up)
     */
    public function clearAll(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        try {
            Notification::truncate(); // Mamafa ny andalana rehetra ao amin'ny tabilao
            return response()->json(['message' => 'Toutes les notifications ont été supprimées.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}