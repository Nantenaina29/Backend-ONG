<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Notification extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'details',
        'is_read' // raha nampianao an'ity
    ];

    // Relation mba hahazoana ny anaran'ny user tany amin'ny Controller
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}