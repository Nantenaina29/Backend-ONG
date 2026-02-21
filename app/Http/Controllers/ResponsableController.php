<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Responsable;
use App\Models\Notification;
use App\Models\Membre;
use Illuminate\Support\Facades\Log;

class ResponsableController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
    
       
            $query = Responsable::whereHas('membre');
    
            if ($user && $user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }
    
      
            $responsables = $query->with(['membre'])->get();
            
          
            $gsList = DB::table('gs')->get()->keyBy('numMenage');
    
            $resultat = $responsables->map(function ($respo) use ($gsList) {
                $membre = $respo->membre; // Efa azo avy amin'ny relation
                $gs = $membre ? $gsList->get($membre->NumMenage) : null;
    
                return [
                    'CodeRespo' => $respo->CodeRespo,
                    'Nom' => $membre->NomMembre ?? 'Tsy fantatra',
                    'Prenom' => $membre->PrenomMembre ?? '',
                    'Sexe' => $membre->Sexe ?? '---',
                    'NomGS' => $gs->nom ?? 'Aucun GS',
                    'Poste' => $respo->Poste ?? 'Aucun poste'
                ];
            });
    
            return response()->json($resultat);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFemmesBureau(Request $request)
    {
        try {
            $user = $request->user();
            
    
            $query = Responsable::whereHas('membre', function($q) {
                    $q->where('Sexe', 'Femme');
                })
                ->where(function($q) {
                    $q->where('Poste', 'ILIKE', 'Pr%sident%')
                      ->orWhere('Poste', 'ILIKE', 'Secr%taire%')
                      ->orWhere('Poste', 'ILIKE', 'Tr%sorier%');
                });
    
            if ($user && $user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }
    
            $responsables = $query->with('membre')->get();
    
            $resultat = $responsables->map(function($respo) {
                return [
                    'Nom' => $respo->membre->NomMembre,
                    'Prenom' => $respo->membre->PrenomMembre,
                    'NomGS' => '---', 
                    'Poste' => $respo->Poste
                   
                ];
            });
    
            return response()->json($resultat);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Poste' => 'required|string|max:100',
        ]);

        try {
            $user = $request->user();

      
            $responsable = Responsable::where('CodeRespo', $id)->firstOrFail();

            if ($user->role !== 'admin' && $responsable->user_id !== $user->id) {
                return response()->json(['error' => 'Action non autorisée!'], 403);
            }

            $membre = Membre::where('NumMembre', $responsable->NumMembre)->first();
            $nomOlona = $membre ? ($membre->NomMembre . " " . $membre->PrenomMembre) : "olona iray";

            // 4. Hanovana ny Poste
            $responsable->Poste = $request->Poste;
            if (!$responsable->user_id) {
                $responsable->user_id = $user->id;
            }
            $responsable->save();

            try {
                Notification::create([
                    'user_id' => $user->id,
                    'action' => 'MODIFICATION',
                    'table_name' => 'responsables',
                    'details' => $user->name . " a mis à jour les fonctions exercées par " . $nomOlona,
                ]);
            } catch (\Exception $notifError) {
                Log::error("Erreur Notif Responsable Update: " . $notifError->getMessage());
            }

            return response()->json([
                'message' => 'Enregistrer avec succès!',
                'data' => $responsable
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur de modification!',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}