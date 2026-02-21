<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Gs;
use App\Models\Membre;
use App\Models\Notification; // Nampiana
use Illuminate\Support\Facades\Log; // Nampiana ho an'ny debug

class GsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user && $user->role === 'admin') {
            $gsList = Gs::orderBy('CodeGS', 'asc')->get();
        } else {
            $gsList = Gs::where('user_id', $user->id)
                         ->orderBy('CodeGS', 'asc')
                         ->get();
        }
    
        $gsList->transform(function ($gs) use ($user) {
            $menages = array_filter(array_map('trim', explode(',', $gs->numMenage)));
            $query = Membre::whereIn('NumMenage', $menages);

            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $gs->effectif = $query->count();
            return $gs;
        });
    
        return response()->json($gsList);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'nom'          => 'required|string|max:150',
            'numMenage'    => 'required|string',
            'dateCreation' => 'nullable|date',
            'commune'      => 'required|string|max:255',
            'fokontany'    => 'required|string|max:255',
            'village'      => 'required|string|max:255',
        ]);

        $commune = trim($validated['commune']);
        $fokontany = trim($validated['fokontany']);
        $village = trim($validated['village']);

        $menages = array_unique(array_filter(array_map('trim', explode(',', $validated['numMenage']))));

        foreach ($menages as $m) {
            $existsQuery = Gs::whereRaw("(',' || REPLACE(\"numMenage\", ' ', '') || ',') LIKE ?", ["%,$m,%"]);
            if ($user->role !== 'admin') {
                $existsQuery->where('user_id', $user->id);
            }

            if ($existsQuery->exists()) {
                return response()->json(['message' => "Le numéro de ménage $m est déjà affecté..."], 422);
            }
        }

        $queryCount = Membre::whereIn('NumMenage', $menages);
        if ($user->role !== 'admin') {
            $queryCount->where('user_id', $user->id);
        }
        $effectifActualise = $queryCount->count();

        try {
            $gs = Gs::create([
                'nom'          => trim($validated['nom']),
                'numMenage'    => implode(',', $menages),
                'dateCreation' => $validated['dateCreation'],
                'commune'      => trim($validated['commune']),
                'fokontany'    => trim($validated['fokontany']),
                'village'      => trim($validated['village']),
                'effectif'     => $effectifActualise,
                'user_id'      => $user->id,
            ]);

            return response()->json(['message' => 'GS créé avec succès', 'data' => $gs], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création', 'error' => $e->getMessage()], 500);
        }

      
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
    
        $request->validate([
            'nom'          => 'required|string|max:150',
            'numMenage'    => 'required|string',
            'dateCreation' => 'nullable|date',
            'commune'      => 'required|string|max:255',
            'fokontany'    => 'required|string|max:255',
            'village'      => 'required|string|max:255',
        ]);
    
        $query = Gs::where('CodeGS', $id);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        $gs = $query->firstOrFail();
        $nomTaloha = $gs->nom; // Tehirizina ny anarana taloha ho an'ny notif
    
        $menages = array_unique(array_map('trim', explode(',', $request->numMenage)));
    
        foreach ($menages as $m) {
            $existsQuery = Gs::where('CodeGS', '!=', $id)
                            ->whereRaw("(',' || REPLACE(\"numMenage\", ' ', '') || ',') LIKE ?", ["%,$m,%"]);
            if ($user->role !== 'admin') {
                $existsQuery->where('user_id', $user->id);
            }
            if ($existsQuery->exists()) {
                return response()->json(['message' => "Le numéro de ménage {$m} est déjà affecté à un otro GS"], 422);
            }
        }

        $queryCount = Membre::whereIn('NumMenage', $menages);
        if ($user->role !== 'admin') {
            $queryCount->where('user_id', $user->id);
        }
        $effectifActualise = $queryCount->count();

        $gs->update([
            'nom'          => $request->nom,
            'numMenage'    => implode(',', $menages),
            'dateCreation' => $request->dateCreation,
            'commune'      => $request->commune,
            'fokontany'    => $request->fokontany,
            'village'      => $request->village,
            'effectif'     => $effectifActualise,
        ]);

        if (!$gs->user_id) {
            $gs->user_id = $user->id;
            $gs->save();
        }

        // --- NOTIFICATION AUTOMATIQUE (MODIFICATION) ---
        try {
            Notification::create([
                'user_id'    => $user->id,
                'action'     => 'MODIFICATION',
                'table_name' => 'gs',
                'details'    => $user->name . " a modifié de GS: " . $nomTaloha , 
            ]);
        } catch (\Exception $e) {
            Log::error("Notif GS Update: " . $e->getMessage());
        }
    
        return response()->json(['message' => 'GS modifié avec succès', 'data' => $gs]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        
        // 1. Tadiavo ilay GS
        $query = Gs::where('CodeGS', $id);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
    
        $gs = $query->firstOrFail();
        $nomHovafana = trim($gs->nom); // trim mialoha

        $ids = \DB::table('reseaux')
        ->whereRaw('"NomGS" ILIKE ?', ["%$nomHovafana%"])
        ->pluck('CodeRS') // Raiso ny ID rehetra
        ->toArray();

        \DB::beginTransaction();
        try {
 
            $gs->last_reseaux = implode(',', $ids);
             $gs->save();

            \DB::table('reseaux')
                ->whereRaw('"NomGS" ILIKE ?', ["%$nomHovafana%"])
                ->update([
                    'NomGS' => \DB::raw("
                        NULLIF(
                            array_to_string(
                                array_remove(
                                    string_to_array(REPLACE(\"NomGS\", ', ', ','), ','), 
                                    (SELECT \"nom\" FROM \"gs\" WHERE \"CodeGS\" = $id)
                                ), 
                            ', '),
                        '')
                    ")
                ]);
    
            // 2. Famafana ny GS
            $gs->delete();
    
            \DB::commit();
            return response()->json(['message' => 'GS supprimé et réseaux nettoyés']);
    
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ======================
    // DATA HELPERS
    // ======================
    public function getNumMenages()
    {
        $user = auth()->user();
        $query = Membre::select('NumMenage')->groupBy('NumMenage')->orderBy('NumMenage');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }

}