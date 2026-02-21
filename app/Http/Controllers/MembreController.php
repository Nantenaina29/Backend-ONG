<?php

namespace App\Http\Controllers;

use App\Models\Membre;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MembreController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); 
    
        // 1. Manomboka ny Query (fa tsy mbola mamoaka vokatra)
        $query = Membre::query();
    
        // 2. Sivanina araka ny Role
        if ($user && $user->role !== 'admin') {
            // Ny user tsotra dia ny azy ihany no hitany
            $query->where('user_id', $user->id);
        } 
        // Raha Admin izy dia tsy asiana "where", ka tonga dia mahita ny rehetra
    
        $membres = $query->orderBy('NumMenage', 'asc')
        ->orderByRaw("CASE WHEN \"Chef\" = 'Chef' THEN 0 ELSE 1 END")
        ->paginate(50);
    
        return response()->json($membres, 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
    
        $validated = $request->validate([
            'NomMembre'      => 'required|string',
            'PrenomMembre'   => 'required|string',
            'AnneeNaissance' => 'required|integer',
            'Sexe'           => 'required|in:Homme,Femme',
            'Chef'           => 'required|in:Chef,Non',
            'NumMenage'      => 'required|integer',
        ]);
    
        $nom = trim($validated['NomMembre']);
        $prenom = trim($validated['PrenomMembre']);
        $numMenage = $validated['NumMenage'];

    
        // 1. Fanamarinana duplicate
        $existsQuery = Membre::where('NomMembre', $nom)
            ->where('PrenomMembre', $prenom)
            ->where('NumMenage', $numMenage);
    
        if ($user->role !== 'admin') {
            $existsQuery->where('user_id', $user->id);
        }
    
        if ($existsQuery->exists()) {
            return response()->json(['message' => 'Ce membre existe déjà.'], 422);
        }
    
        // 2. Fanamarinana Chef de Ménage
        if ($request->Chef === 'Chef') {
            $chefQuery = Membre::where('NumMenage', $numMenage)
                ->where('Chef', 'Chef');
    
            if ($user->role !== 'admin') {
                $chefQuery->where('user_id', $user->id);
            }
        
            if ($chefQuery->exists()) {
                return response()->json([
                    'message' => "Un Chef existe déjà pour le ménage n°" . $numMenage
                ], 422);
            }
        }
    
        try {
            // Manomboka ny Transaction
            return DB::transaction(function () use ($validated, $nom, $prenom, $numMenage, $user) {
                
                // A. Mamorona ny Membre
                $membre = Membre::create([
                    'NomMembre' => $nom,
                    'PrenomMembre' => $prenom,
                    'AnneeNaissance' => $validated['AnneeNaissance'],
                    'Sexe' => $validated['Sexe'],
                    'Chef' => $validated['Chef'],
                    'NumMenage' => $numMenage,
                    'user_id' => $user->id,
                ]);

                // B. updateOrCreate Responsable (Mba tsy hisy Unique Violation)
                \App\Models\Responsable::updateOrCreate(
                    ['NumMembre' => $membre->NumMembre], 
                    [
                        'Poste'   => 'Membres',
                        'user_id' => $user->id,
                    ]
                );

                // C. updateOrCreate Formation
                \App\Models\Formation::updateOrCreate(
                    ['NumMembre' => $membre->NumMembre],
                    [
                        'user_id'               => $user->id,
                        'gestionsimplifiee'     => false,
                        'agrosol'               => false,
                        'agroeco'               => false,
                        'agroeau'               => false,
                        'agrovegetaux'          => false,
                        'productionsemence'     => false,
                        'nutrition'             => false,
                        'nutritioneau'          => false,
                        'nutritionalimentaire'  => false,
                        'conservationproduit'   => false,
                        'transformationproduit' => false,
                        'genre'                 => false,
                        'epracc'                => false,
                        'autonomie'             => 'Non',
                    ]
                );

                // D. Notification
                Notification::create([
                    'user_id'    => $user->id,
                    'action'     => 'CREATION',
                    'table_name' => 'membres',
                    'details'    => $user->name . " a ajouté " . $nom . " " . $prenom,
                ]);

                return response()->json([
                    'message' => 'Membre, Responsable et Formation créés avec succès',
                    'data'    => $membre
                ], 201);
            });

        } catch (\Exception $e) {
            // Tsy mila DB::rollBack() eto satria ny DB::transaction dia efa manao izany ho azy raha misy error
            Log::error("Erreur Ajout Membre: " . $e->getMessage());
            return response()->json([
                'message' => 'Fahadisoana',
                'sql_error' => $e->getMessage() 
            ], 500);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $query = Membre::where('NumMembre', $id);

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $membre = $query->first();

        if (!$membre) {
            return response()->json(['message' => 'Membre non trouvé'], 404);
        }

        return response()->json($membre);
    }

    public function update(Request $request, $numMembre)
    {
        $user = auth()->user(); 

        DB::beginTransaction();
        
        $query = Membre::where('NumMembre', $numMembre);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        $membre = $query->first();
    
        if (!$membre) {
            return response()->json(['message' => 'Membre non trouvé ou accès refusé'], 404);
        }
    
        $validated = $request->validate([
            'NomMembre'      => 'required|string',
            'PrenomMembre'   => 'required|string',
            'AnneeNaissance' => 'required|integer',
            'Sexe'           => 'required|string',
            'Chef'           => 'required|string',
            'NumMenage'      => 'required|integer',
        ]);

        $nom = trim($validated['NomMembre']);
        $prenom = trim($validated['PrenomMembre']);
        $numMenage = $validated['NumMenage'];

        // 1. Fanamarinana duplicate (ankoatra an'ity mpikambana ity)
        $existsQuery = Membre::where('NomMembre', $nom)
            ->where('PrenomMembre', $prenom)
            ->where('NumMenage', $numMenage)
            ->where('NumMembre', '<>', $numMembre);
        
        if ($user->role !== 'admin') {
            $existsQuery->where('user_id', $user->id);
        }

        if ($existsQuery->exists()) {
            return response()->json(['message' => 'Un autre membre avec ce nom existe déjà.'], 422);
        }
    
        // 2. Tehirizina ny anarana taloha ho an'ny notif
        $nomTaloha = $membre->NomMembre . " " . $membre->PrenomMembre;
        
        // 3. Update
        $membre->update($validated);

        // 4. Notification
        try {
            Notification::create([
                'user_id' => $user->id,
                'action' => 'MODIFICATION',
                'table_name' => 'membres',
                'details' => $user->name . " a modifié les informations de " . $nomTaloha,
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Avadika ho tsinontsinona raha nisy diso
            return response()->json(['message' => 'Erreur', 'error' => $e->getMessage()], 500);
        }
    
        DB::commit();

        return response()->json([
            'message' => 'Membre modifié avec succès',
            'data'    => $membre
        ]);
    }

    public function destroy($numMembre)
    {
        $user = auth()->user();
        
        // 1. Tadiavina ilay membre miaraka amin'ny fiarovana user_id
        $query = \App\Models\Membre::where('NumMembre', $numMembre);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        $membre = $query->first();
    
        if (!$membre) {
            return response()->json(['message' => 'Suppression impossible : Membre non trouvé'], 404);
        }
    
        $nomHovafana = $membre->NomMembre . " " . $membre->PrenomMembre;
    
        try {
            DB::beginTransaction();
    
            // 2. Soft Delete ny Formations sy Responsables (Eloquent mode)
            // Ampiasao ny primary key mifanaraka amin'ny tabilao tsirairay
            \App\Models\Formation::where('NumMembre', $numMembre)->delete();
            \App\Models\Responsable::where('NumMembre', $numMembre)->delete();
            
            // 3. Soft Delete ilay Membre
            $membre->delete();
    
            // 4. Notification
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'action' => 'SUPPRESSION',
                'table_name' => 'membres',
                'details' => $user->name . " a supprimé " . $nomHovafana,
            ]);
    
            DB::commit();
            return response()->json(['message' => "Deplacée dans la corbeille  $nomHovafana."]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur suppression', 'error' => $e->getMessage()], 500);
        }
    } 
}