<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    protected $fillable = [
        'commande_id',
        'status',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
