<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class UserController extends Controller
{
    // 1. Manova ny mombamomba (Nom & Email)
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($data);

        return response()->json(['message' => 'Profil mis à jour avec succès', 'user' => $user]);
    }

    // 2. Manova ny Sary (Update Photo)
    public function updatePhoto(Request $request)
    {
        try {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240', 
            ]);

            $user = $request->user();

            if ($request->hasFile('photo')) {
                // 1. Famafana ny sary taloha
                if ($user->photo) {
                    // Esorina ny '/storage/' mba hahazoana ny tena path
                    $oldPath = str_replace('/storage/', '', $user->photo);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                // 2. Fitahirizana vaovao
                $path = $request->file('photo')->store('avatars', 'public');
                
                // 3. PostgreSQL FIX: Ampiasao ity fomba ity hitahirizana ny URL
                // Aza mampiasa backslash na fomba hafa, fa URL mivantana
                $user->photo = asset('storage/' . $path); 
                
                // 4. Force save
                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Sary voasolo!',
                    'photo_url' => $user->photo,
                    'user' => $user // Avereno ny user iray manontolo
                ]);
            }
        } catch (\Exception $e) {
            // Ity no mamoaka ny tena antony ao amin'ny Inspecter F12
            return response()->json([
                'status' => 'error',
                'message' => "Fahadisoana: " . $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    // 3. Manova ny Teny miafina
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect'], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Mot de passe modifié avec succès']);
    }
}