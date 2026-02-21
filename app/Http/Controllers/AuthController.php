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
use Exception;

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
            'password' => 'required|string|min:6', // Azonao ampiana '|confirmed' raha misy input confirm_password
            'pincode'  => 'required|string'
        ]);

        // Verification du code PIN (Mety kokoa raha avy amin'ny .env)
        $validPin = "ADMIN2026"; 

        if ($request->pincode !== $validPin) {
            return response()->json([
                'status'  => 'error',
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

            return response()->json([
                'status'  => 'success',
                'message' => 'Compte créé avec succès',
                'user'    => $user
            ], 201);

        } catch (Exception $e) {
            Log::error("Erreur lors de l'inscription: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Une erreur interne est survenue lors de la création du compte.'
            ], 500);
        }
    }

    /**
     * Connexion de l'utilisateur et génération de Token
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Identifiants incorrects. Veuillez réessayer.'
            ], 401);
        }

        $user = Auth::user();
        
        // Supprimer les anciens tokens pour éviter l'accumulation (Optionnel mais recommandé)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'     => 'success',
            'message'    => 'Connexion réussie',
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Déconnexion (Suppression des tokens)
     */
    public function logout(Request $request)
    {
        try {
            // Supprime uniquement le token actuel
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Déconnexion réussie'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }

    /**
     * Récupérer l'utilisateur authentifié
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user'   => $request->user()
        ]);
    }

    // Any amin'ny AuthController.php

    public function forgotPassword(Request $request) 
{
    $request->validate(['email' => 'required|email']);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Cette adresse email n\'existe pas.'], 404);
    }

    // 1. Mamorona password vaovao
    $newPassword = Str::random(40); 

    // 2. Tehirizina ao amin'ny Database (Hashed)
    $user->password = Hash::make($newPassword);
    $user->save();

    try {
        // 3. Mandefa ny Email
        Mail::to($user->email)->send(new ForgotPasswordMail($newPassword));
        
        return response()->json([
            'message' => 'Un nouveau mot de passe a été envoyé à votre adresse email.'
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage()
        ], 500);
    }
}
}