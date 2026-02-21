<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Ampio ity

class TrashController extends Controller
{
    protected function getModel($table) {
        $mapping = [
            'membres'      => \App\Models\Membre::class,
            'gs'           => \App\Models\Gs::class,
            'reseaux'      => \App\Models\Reseau::class,
            'formations'   => \App\Models\Formation::class,
            'responsables' => \App\Models\Responsable::class,
        ];
        if (!isset($mapping[$table])) throw new \Exception("Table non reconnue");
        return $mapping[$table];
    }

    // Function fanampiny hamantarana ny Primary Key marina
    protected function getPrimaryKey($table) {
        return match ($table) {
            'gs'           => 'CodeGS',
            'reseaux'      => 'CodeRS',
            'membres'      => 'NumMembre',
            'responsables' => 'CodeRespo',
            'formations'   => 'Codeformation',
            default        => 'id',
        };
    }

    public function index($table) {
        try {
            $modelClass = $this->getModel($table);
            
            // 1. Manomboka ny query amin'ny onlyTrashed()
            $query = $modelClass::onlyTrashed();
    
            // 2. Raha responsables na formations dia ampiarahina amin'ny membres
            if ($table === 'responsables' || $table === 'formations') {
                $query->join('membres', "$table.NumMembre", "=", "membres.NumMembre")
                      // Mila select mazava tsara mba tsy hifangaro ny ID
                      ->select("$table.*", "membres.NomMembre", "membres.PrenomMembre");
            }
    
            // 3. Averina ny vokatra
            return response()->json($query->get());
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    public function restore($table, $id) {
        try {
            $modelClass = $this->getModel($table);
            $pk = $this->getPrimaryKey($table);
    
            $item = $modelClass::onlyTrashed()->where($pk, $id)->first();
    
            if (!$item) {
                return response()->json([
                    'message' => "Error: N'existe pas dans la corbeille"
                ], 404);
            }
    
            \DB::beginTransaction(); 
    
            // 1. Averina ilay item (Membre, Gs, sns.)
            $item->restore();
    
            // --- FANARENANA NY FIFANDRAISANA (Logic fanampiny) ---
    
            // A. Raha Membre no averina, tadiavo raha nisy Responsable na Formation mifandray aminy
            if ($table === 'membres') {
                $numMembre = $id;
    
                // Averina ny maha-responsable azy raha nisy
                \App\Models\Responsable::onlyTrashed()
                    ->where('NumMembre', $numMembre)
                    ->restore();
    
                // Averina koa ny formations-ny raha nisy
                \App\Models\Formation::onlyTrashed()
                    ->where('NumMembre', $numMembre)
                    ->restore();
            }
    
            // B. Logic efa nisy ho an'ny GS (GS miverina amin'ny Reseaux)
            if ($table === 'gs') {
                $nomHiverina = trim($item->nom);
                if (!empty($item->last_reseaux)) {
                    $ids = explode(',', $item->last_reseaux);
                    \DB::table('reseaux')
                        ->whereIn('CodeRS', $ids)
                        ->update([
                            'NomGS' => \DB::raw("
                                CASE 
                                    WHEN \"NomGS\" IS NULL OR TRIM(\"NomGS\") = '' THEN '$nomHiverina'
                                    ELSE TRIM(BOTH ', ' FROM \"NomGS\") || ', ' || '$nomHiverina'
                                END
                            ")
                        ]);
                    $item->last_reseaux = null;
                    $item->save();
                }
            }
    
            \DB::commit();
            return response()->json(['message' => 'Restauré avec succès dans toutes les tables']);
    
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    public function forceDelete($table, $id) {
        try {
            $modelClass = $this->getModel($table);
            $pk = $this->getPrimaryKey($table);

            $item = $modelClass::onlyTrashed()->where($pk, $id)->firstOrFail();
    
            \Illuminate\Support\Facades\DB::beginTransaction();

            if ($table === 'gs') {

                
                $numMenageGS = $item->numMenage; 
                $nomGS = $item->nom;

                \App\Models\Responsable::withTrashed()
                    ->whereHas('membre', function($query) use ($numMenageGS) {
                        $query->where('NumMenage', $numMenageGS);
                    })->forceDelete();
    
                    if ($table === 'gs') {
                        $codeGS = $id;
                        $nomGS = $item->nom; 

                        $numMenageGS = $item->numMenage; 
                        
                        if ($numMenageGS) {
                            \App\Models\Responsable::withTrashed()
                                ->whereHas('membre', function($query) use ($numMenageGS) {
                                    $query->where('NumMenage', $numMenageGS);
                                })->forceDelete();
                        }

                        if ($nomGS) {
                            \Illuminate\Support\Facades\DB::table('reseaux')
                                ->whereRaw(' "NomGS" LIKE ? ', ["%{$nomGS}%"])
                                ->delete(); 
                        }
                    }
            }
    
            // 3. Fafana amin'izay ilay GS
            $item->forceDelete();
    
            \Illuminate\Support\Facades\DB::commit();
            
            return response()->json([
                'message' => 'Supprimé definitivement le ' . $table
            ]);
    
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}