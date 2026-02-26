<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur avec vérification PIN
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'pincode'  => 'required|string'
        ]);

        // PIN Verification (ADMIN2026)
        $validPin = "ADMIN2026"; 
        if ($request->pincode !== $validPin) {
            return response()->json([
                'message' => 'Le Code PIN d\'autorisation est incorrect.'
            ], 403);
        }

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role ?? 'user',
            ]);

            // ✅ FRONTEND COMPATIBLE
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                    'photo' => $user->photo
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error("Erreur Inscription: " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'inscription'
            ], 500);
        }
    }

    /**
     * Connexion - FRONTEND COMPATIBLE {token, user}
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email na mot de passe diso!'
            ], 401);
        }

        $user = Auth::user();
        
        // Nettoyer anciens tokens
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ FRONTEND EXPECT IO!
        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role ?? 'user',
                'photo' => $user->photo
            ]
        ], 200);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Déconnexion réussie'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }

    /**
     * Utilisateur connecté
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function forgotPassword(Request $request) 
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email tsy misy!'], 404);
        }

        $newPassword = Str::random(12);
        $user->password = Hash::make($newPassword);
        $user->save();

        try {
            Mail::to($user->email)->send(new ForgotPasswordMail($newPassword));
            return response()->json([
                'message' => 'Mot de passe vaovao voalaza tamin\'ny email!'
            ]);
        } catch (\Exception $e) {
            Log::error('Forgot password email error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Email tsy voalaza, fa password vaovao: ' . $newPassword
            ]);
        }
    }
}
