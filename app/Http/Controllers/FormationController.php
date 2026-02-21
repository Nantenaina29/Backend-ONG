<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formation;
use App\Models\Membre;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); 
        $search = $request->query('search');
        
        $query = DB::table('membres')
            // 1. ZAVA-DEHIBE: Sivana mba tsy hamoaka ny ao anaty corbeille
            ->whereNull('membres.deleted_at') 
            ->leftJoin('formations', function($join) {
                $join->on('membres.NumMembre', '=', 'formations.NumMembre')
                     // Sivana koa raha sanatria misy formation efa voafafa (Soft Delete)
                     ->whereNull('formations.deleted_at'); 
            })
            ->select(
                'membres.NumMembre',
                'membres.PrenomMembre as prenom_membre',
                'formations.codeformation',
                'formations.gestionsimplifiee',
                'formations.agrosol',
                'formations.agroeau',
                'formations.agrovegetaux',
                'formations.agroeco',
                'formations.productionsemence',
                'formations.nutritioneau',
                'formations.nutritionalimentaire',
                'formations.nutrition',
                'formations.conservationproduit',
                'formations.transformationproduit',
                'formations.genre',
                'formations.epracc',
                'formations.autre',
                'formations.autonomie'
            );
    
        // 2. Sivana araka ny Role
        if ($user && $user->role !== 'admin') {
            $query->where('membres.user_id', $user->id);
        }
    
        // 3. Fikarohana (Search)
        if (!empty($search)) {
            $query->where('membres.PrenomMembre', 'ILIKE', "%{$search}%");
        }
    
        $data = $query->distinct('membres.NumMembre')
            ->orderBy('membres.NumMembre', 'asc')
            ->get();
    
        return response()->json(['data' => $data]);
    }
    
    public function store(Request $request)
    {
        $user = $request->user();
        $numMembre = $request->NumMembre;
        $queryMembre = Membre::where('NumMembre', $numMembre);

        // Ny user dia afaka manova ny azy ihany, fa ny admin afaka daholo
        if ($user->role !== 'admin') {
            $queryMembre->where('user_id', $user->id);
        }

        $checkMembre = $queryMembre->first();

        if (!$checkMembre) {
            return response()->json([
                'status' => 'error',
                'message' => 'Action non autorisée sur ce membre.'
            ], 403);
        }

        $data = $request->all();
        $existingFormation = Formation::where('NumMembre', $numMembre)->first();
        $isUpdate = $existingFormation ? true : false;

        if ($existingFormation) {
            $data['user_id'] = $existingFormation->user_id;
        } else {
            $data['user_id'] = $checkMembre->user_id ?? $user->id;
        }

        // 1. Tanterahina aloha ny fampidirana data
        $formation = Formation::updateOrCreate(
            ['NumMembre' => $numMembre],
            $data
        );

        // 2. LOGIQUE NOTIFICATION AUTOMATIQUE (Try-Catch mba tsy hampiato ny asa)
        try {
            // Fampandrenesana raha misy fanovana na fampidirana vaovao
            $actionType = $isUpdate ? 'MODIFICATION' : 'AJOUT';
            $msg = $isUpdate ? "a modifié la formation à " : "a donnée la formation pour ";

            Notification::create([
                'user_id' => $user->id,
                'action' => $actionType,
                'table_name' => 'formations',
                'details' => $user->name . " " . $msg . $checkMembre->PrenomMembre,
            ]);
        } catch (\Exception $e) {
            // Raha misy olana ny notif (ohatra database full), manoratra ao amin'ny log fotsiny
            Log::error("Erreur Notification Formation: " . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => $isUpdate ? 'Formation modifiée avec succès!' : 'Formation ajoutée avec succès!',
            'data' => $formation
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        try {
            $queryMembre = Membre::where('NumMembre', $id);

            if ($user->role !== 'admin') {
                $queryMembre->where('user_id', $user->id);
            }

            $membre = $queryMembre->firstOrFail();
            $nomMembre = $membre->PrenomMembre;

            DB::beginTransaction();
            
            // Fafao ny formation mifandray aminy
            DB::table('formations')->where('NumMembre', $id)->delete();

            // Fafao ny membre
            $membre->delete();

            // --- LOGIQUE NOTIFICATION AUTOMATIQUE ---
            try {
                Notification::create([
                    'user_id' => $user->id,
                    'action' => 'SUPPRESSION',
                    'table_name' => 'membres',
                    'details' => $user->name . " a supprimé " . $nomMembre . " et ses formations.",
                ]);
            } catch (\Exception $notifError) {
                Log::error("Erreur Notification Suppression: " . $notifError->getMessage());
            }

            DB::commit();
            return response()->json(['message' => 'Suppression réussie!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}