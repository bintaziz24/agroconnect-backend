<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Livraison;

class LivraisonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $livraisons = Livraison::with('commande.client')->latest()->get();
        } elseif ($user->role === 'agriculteur') {
            $agriculteur = $user->agriculteur;
            $livraisons = Livraison::whereHas('commande.lignesCommande.produit', function ($q) use ($agriculteur) {
                $q->where('agriculteur_id', $agriculteur->id);
            })->with(['commande.lignesCommande' => function ($q) use ($agriculteur) {
                $q->whereHas('produit', function ($qp) use ($agriculteur) {
                    $qp->where('agriculteur_id', $agriculteur->id);
                })->with('produit');
            }, 'commande.client'])->latest()->get();
        } else {
            // Client
            $livraisons = Livraison::whereHas('commande', function ($q) use ($user) {
                $q->where('client_id', $user->id);
            })->with('commande.lignesCommande.produit')->latest()->get();
        }

        return response()->json($livraisons);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|string|in:en_attente,preparation,expediee,en_cours,livree,annulee',
        ]);

        $livraison = Livraison::findOrFail($id);
        $livraison->status = $request->status;
        $livraison->save();

        // Synchroniser le statut de la commande
        $commande = $livraison->commande;
        $commande->statut = $request->status;
        $commande->save();

        return response()->json($livraison->load('commande'));
    }
}
