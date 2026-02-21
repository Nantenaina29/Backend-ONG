<?php

namespace App\Http\Controllers;

use App\Models\Reseau;
use App\Models\GS;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReseauController extends Controller
{
    // Lisitry ny reseaux (Miaraka amin'ny sivana Admin/User)
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = $request->query('q');
        
        $query = Reseau::query();

        // 1. Sivana araka ny Role
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        // 2. Sivana araka ny fikarohana (Search)
        if ($q) {
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('NomRS', 'like', "%{$q}%")
                         ->orWhere('NomGS', 'like', "%{$q}%");
            });
        }

        return $query->orderBy('CodeRS', 'desc')->paginate(10);
    }

    // Mamorona reseau vaovao
    public function store(Request $request)
    {
        $user = auth()->user();
        $validator = \Validator::make($request->all(), [
            'NomRS' => 'required|string',
            'NomGS' => 'required',
            'DateCreation' => 'nullable|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Misy fepetra tsy feno',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            $userId = $user->id;
            $nomRS = trim($request->NomRS);
            $dateCreation = $request->DateCreation;
    
            $nomGSString = is_array($request->NomGS) 
                ? implode(', ', $request->NomGS) 
                : trim($request->NomGS);
    
            $exists = Reseau::where('user_id', $userId)
                ->where('NomRS', 'ILIKE', $nomRS)
                ->where('NomGS', 'ILIKE', $nomGSString)
                ->where('DateCreation', $dateCreation)
                ->exists();
    
            if ($exists) {
                return response()->json([
                    'message' => 'Réseau est déjà existe!.'
                ], 422); 
            }
    
            $data = $request->all();
            $data['NomRS'] = $nomRS;
            $data['NomGS'] = $nomGSString;
            $data['user_id'] = $userId;
            
            $data['Autonomie'] = ($request->Activite && $request->Plaidoyer && $request->Plan) 
                                 ? 'Autonome' : 'Non';
    
            $reseau = Reseau::create($data);

            
            return response()->json($reseau, 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur du server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
{
    $user = auth()->user();
    
    // 1. Fitadiavana ny réseau
    $reseau = \App\Models\Reseau::where('CodeRS', $id)->first();

    if (!$reseau) {
        return response()->json(['message' => 'Réseau non trouvé'], 404);
    }

    $role = strtolower(trim($user->role));
    
    if ($role === 'admin') {
       
    } else {
   
        if ($reseau->user_id != $user->id) {
            return response()->json([
                'message' => 'Action non autorisée',
                'debug' => [
                    'votre_id' => $user->id,
                    'votre_role' => $user->role,
                    'tompo_id' => $reseau->user_id
                ]
            ], 403);
        }
    }

    // 3. VALIDATION
    $request->validate([
        'NomRS' => 'required|string|max:255',
        'Activite' => 'required|boolean',
        'Plaidoyer' => 'required|boolean',
        'Plan' => 'required|boolean',
    ]);

    // 4. UPDATE
    try {
        $reseau->update($request->all());
        return response()->json(['message' => 'Mety tsara!', 'data' => $reseau]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur SQL', 'error' => $e->getMessage()], 500);
    }
}
    public function destroy($id)
    {
        $user = auth()->user();
        
        // Fitadiavana mampiasa CodeRS
        $reseau = Reseau::where('CodeRS', $id)->first();

        if (!$reseau) {
            return response()->json(['message' => 'Réseau non trouvé'], 404);
        }

        if (strtolower($user->role) !== 'admin' && $reseau->user_id != $user->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $nomHovafana = $reseau->NomRS;

        try {
     
            Notification::create([
                'user_id' => $user->id,
                'action' => 'SUPPRESSION',
                'table_name' => 'reseaux',
                'details' => $user->name . " a supprimé le réseau: " . $nomHovafana,
            ]);
            
            $reseau->delete();
            return response()->json(['message' => 'Supprimé avec succès']);
            
        } catch (\Exception $e) {
            Log::error("Erreur Destruction Reseau: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression'], 500);
        }
    }
    public function gsList()
    {
        return response()->json(GS::select('CodeGS', 'nom')->get());
    }
}