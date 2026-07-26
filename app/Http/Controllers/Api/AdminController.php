<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Agriculteur;
use App\Models\Produit;
use App\Models\Commande;

class AdminController extends Controller
{
    /**
     * Get list of all users with agriculteur profiles.
     */
    public function utilisateurs(Request $request)
    {
        $users = User::with('agriculteur')->latest()->get();
        return response()->json($users);
    }

    /**
     * Get global platform statistics.
     */
    public function statistiques(Request $request)
    {
        $totalClients = User::where('role', 'client')->count();
        $totalAgriculteurs = Agriculteur::count();
        $totalProduits = Produit::count();
        $totalCommandes = Commande::count();
        $totalRevenus = Commande::where('statut', '!=', 'annulee')->sum('montant_total');

        return response()->json([
            'clients'      => $totalClients,
            'agriculteurs' => $totalAgriculteurs,
            'produits'     => $totalProduits,
            'commandes'    => $totalCommandes,
            'revenus'      => floatval($totalRevenus),
        ]);
    }

    /**
     * Validate an agricultural producer's profile.
     */
    public function validerAgriculteur(Request $request, string $id)
    {
        $agriculteur = Agriculteur::findOrFail($id);
        $agriculteur->statut_validation = 'validé';
        $agriculteur->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Profil agriculteur validé avec succès.',
            'agriculteur'  => $agriculteur->load('user'),
        ]);
    }
}
