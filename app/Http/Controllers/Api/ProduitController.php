<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Categorie;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::with(['agriculteur', 'categorie'])
                        ->where('stock', '>', 0);

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('categorie')) {
            $query->where('categorie_id', $request->categorie);
        }

        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }

        $produits = $query->latest()->paginate(12);

        return response()->json($produits);
    }

    public function show($id)
    {
        $produit = Produit::with(['agriculteur', 'categorie'])->findOrFail($id);
        return response()->json($produit);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'          => 'required|string',
            'prix'         => 'required|numeric',
            'stock'        => 'required|integer',
            'categorie_id' => 'required|exists:categories,id',
            'unite'        => 'required|string',
        ]);

        $produit = Produit::create([
            'agriculteur_id' => auth()->user()->agriculteur->id,
            'categorie_id'   => $request->categorie_id,
            'nom'            => $request->nom,
            'description'    => $request->description,
            'prix'           => $request->prix,
            'stock'          => $request->stock,
            'unite'          => $request->unite,
        ]);

        return response()->json($produit, 201);
    }

    public function update(Request $request, $id)
    {
        $produit = Produit::findOrFail($id);
        $produit->update($request->all());
        return response()->json($produit);
    }

    public function destroy($id)
    {
        Produit::findOrFail($id)->delete();
        return response()->json(['message' => 'Produit supprimé']);
    }

    public function dashboard()
    {
        $agriculteur = auth()->user()->agriculteur;
        
        if (!$agriculteur) {
            return response()->json(['message' => 'Profil agriculteur introuvable.'], 404);
        }

        // Nombre total de commandes contenant ses produits
        $totalCommandes = \App\Models\Commande::whereHas('lignesCommande.produit', function($q) use ($agriculteur) {
            $q->where('agriculteur_id', $agriculteur->id);
        })->count();

        // Revenu exact (somme de quantite * prix_unitaire de ses produits uniquement)
        $revenus = \App\Models\LigneCommande::whereHas('produit', function ($q) use ($agriculteur) {
            $q->where('agriculteur_id', $agriculteur->id);
        })->whereHas('commande', function ($q) {
            $q->where('statut', '!=', 'annulee');
        })->selectRaw('SUM(quantite * prix_unitaire) as total')->value('total') ?? 0;

        // Les 5 dernières commandes contenant ses produits
        $dernieresCommandes = \App\Models\Commande::whereHas('lignesCommande.produit', function($q) use ($agriculteur) {
            $q->where('agriculteur_id', $agriculteur->id);
        })->with(['lignesCommande' => function ($q) use ($agriculteur) {
            $q->whereHas('produit', function ($qp) use ($agriculteur) {
                $qp->where('agriculteur_id', $agriculteur->id);
            })->with('produit');
        }, 'client', 'livraison'])->latest()->take(5)->get();

        return response()->json([
            'commandes'           => $totalCommandes,
            'revenus'             => floatval($revenus),
            'produits'            => Produit::where('agriculteur_id', $agriculteur->id)->count(),
            'dernieres_commandes' => $dernieresCommandes,
        ]);
    }

    public function agriculteurs()
    {
        $agriculteurs = \App\Models\Agriculteur::with('user')
                        ->where('statut_validation', 'validé')
                        ->get();
        return response()->json($agriculteurs);
    }
}