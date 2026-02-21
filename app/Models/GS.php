<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
 

class Gs extends Model
{
    use SoftDeletes;
    protected $table = 'gs';
    protected $primaryKey = 'CodeGS';
    protected $dates = ['deleted_at'];

    public $timestamps = true;

    protected $fillable = [
        'nom',
        'numMenage',
        'effectif',
        'dateCreation',
        'commune',     // nomcommune
        'fokontany',   // nomfkt
        'village',
        'user_id',    
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
}
