<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Formation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formations';

    // Primary key an'ity tabilao ity
    protected $primaryKey = 'codeformation';

    // Satria mety ho string na integer ny codeformation
    public $incrementing = true; 

    public $timestamps = false; 

    protected $fillable = [
        'NumMembre', 
        'user_id', 
        'gestionsimplifiee', 
        'agrosol', 
        'agroeco', 
        'agroeau', 
        'agrovegetaux', 
        'productionsemence', 
        'nutrition', 
        'nutritioneau', 
        'nutritionalimentaire', 
        'conservationproduit', 
        'transformationproduit', 
        'genre', 
        'epracc', 
        'autre', 
        'autonomie'
    ];

    /**
     * Fifandraisana miverina amin'ny Membre
     */
    public function membre()
    {
        return $this->belongsTo(Membre::class, 'NumMembre', 'NumMembre');
    }
}