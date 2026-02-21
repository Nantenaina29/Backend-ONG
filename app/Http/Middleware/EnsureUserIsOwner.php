<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Membre;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $id = $request->route('id'); // id nalefa ao amin'ny route
        $membre = Membre::find($id);

        if ($membre && $membre->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return $next($request);
    }
}
