<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agriculteur extends Model
{
    protected $fillable = [
        'user_id',
        'localisation',
        'latitude',
        'longitude',
        'description',
        'statut_validation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
