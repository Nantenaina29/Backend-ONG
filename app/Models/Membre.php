<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membre extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'membres';
    protected $primaryKey = 'NumMembre';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'NomMembre', 'PrenomMembre', 'AnneeNaissance', 'Sexe', 'Chef', 'NumMenage', 'user_id'
    ];


    protected static function booted()
    {
        // A. REHEFA MAMAFA (SOFT DELETE)
        static::deleted(function ($membre) {
            
            \App\Models\Formation::where('NumMembre', $membre->NumMembre)->delete();
            \App\Models\Responsable::where('NumMembre', $membre->NumMembre)->delete();
        });

        // B. REHEFA MANAO RESTAURER (RESTORE)
        static::restored(function ($membre) {
           
            \App\Models\Formation::withTrashed()
                ->where('NumMembre', $membre->NumMembre)
                ->restore();

            \App\Models\Responsable::withTrashed()
                ->where('NumMembre', $membre->NumMembre)
                ->restore();

        });

        // C. REHEFA MAMAFA TANTERAKA (FORCE DELETE)
            static::forceDeleted(function ($membre) {
                \App\Models\Formation::withTrashed()
                    ->where('NumMembre', $membre->NumMembre)
                    ->forceDelete();

                \App\Models\Responsable::withTrashed()
                    ->where('NumMembre', $membre->NumMembre)
                    ->forceDelete();
            });
    }
}