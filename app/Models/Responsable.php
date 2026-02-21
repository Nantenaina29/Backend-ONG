<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Responsable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'responsables';
    protected $primaryKey = 'CodeRespo'; 
    public $incrementing = true;

    protected $fillable = [
        'NumMembre',
        'Poste',
        'user_id',
    ];

    // Tsy maintsy atao anaty function ny relation
    public function membre()
    {
        return $this->belongsTo(Membre::class, 'NumMembre', 'NumMembre');
    }
}