<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Reseau extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reseaux';
    
    // Satria CodeRS no lakile fa tsy 'id'
    protected $primaryKey = 'CodeRS';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'NomRS',
        'NomGS', 
        'DateCreation',
        'Activite',
        'Plaidoyer',
        'Plan',
        'Autonomie',
        'user_id'
    ];

    protected $casts = [
        'DateCreation' => 'date',
        'Activite' => 'boolean',
        'Plaidoyer' => 'boolean',
        'Plan' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            $model->Autonomie = ($model->Activite && $model->Plaidoyer && $model->Plan) ? 'Autonome' : 'Non';
        });
    
        static::updating(function ($model) {
            $model->Autonomie = ($model->Activite && $model->Plaidoyer && $model->Plan) ? 'Autonome' : 'Non';
        });
    }
}