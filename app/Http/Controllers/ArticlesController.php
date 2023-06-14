<?php

namespace App\Http\Controllers;

use App\Models\Articles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArticlesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = Articles::all();
        if (count($articles) <= 0) {
            return response()->json(["message" => "Pas d'articles"], 404);
        }
        return response()->json($articles, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // recuperer l'id du createur connecté
        $idCreateur = Auth::id();

        // Valider les données de la requête
        $articleDonnee = $request->validate([
            'nomArticle' => ["required", "string", "max:100"],
            'description' => ["required", "string", "max:255"],
            'photo' => ["nullable", "image", "max:2048"],
            'prixArticle' => ["required", "numeric", "min:0"],
            'reference' => ["required", "integer", "min:0"],
            'taille' => ["required", "string", "max:10"],
            'couleur' => ["required", "string", "max:30"],
            'categorie' => ["required", "string", "max:30"],
        ]);

        // Ajouter l'id du createur connecté
        $articleDonnee['idCreateur'] = $idCreateur;

        // Vérifier si une image a été téléchargée
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $request->file('photo')->storeAs('public', $filename);

            // Récupérer le chemin de stockage relatif de l'image
            $articleDonnee['photoArticle'] = str_replace('public/', '', $path);
        }

        // Créer un nouvel article
        Articles::create($articleDonnee);

        // Redirection ou autre traitement
        return response()->json([
            'message' => 'Article créé avec succès',
            'status' => 201,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Articles  $articles
     * @return \Illuminate\Http\Response
     */
    public function show($idArticle)
    {
        // method 'with' is used to get the data from the relationship
        $article = Articles::with('createur')
            ->where('idArticle', $idArticle)
            ->first();
        if (!$article) {
            return response()->json(["message" => "Article non trouvé"], 404);
        } else {
            return response()->json($article, 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Articles  $articles
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idArticle)
    {
        // Valider les données de la requête
        $articleValidation = $request->validate([
            'nomArticle' => ["string", "max:100"],
            'description' => ["string", "max:255"],
            'photo' => ["nullable", "image", "max:2048"],
            'prixArticle' => ["numeric", "min:0"],
            'reference' => ["integer", "min:0"],
            'taille' => ["string", "max:10"],
            'couleur' => ["string", "max:30"],
            'categorie' => ["string", "max:30"],
            'idCreateur' => ["required", "integer"],
        ]);

        // Vérifier si une image a été téléchargée
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $request->file('photo')->storeAs('public', $filename);

            // Récupérer le chemin de stockage relatif de l'image
            $articleDonnee['photoArticle'] = str_replace('public/', '', $path);
        }

        // verifier si l'article existe
        $article = Articles::findOrfail($idArticle);
        // if (!$article) {
        //     return response()->json(["message" => "Article non trouvé avec cet id $idArticle"], 404);
        // }
        // Vérifier si l'utilisateur est autorisé à modifier l'article
        if ($article->idCreateur != $request->createur()->id) {
            return response()->json(["message" => "Vous n'êtes pas autorisé à modifier cet article"], 401);
        }

        // Récupérer l'id de l'article dans la requête
        // $article = Articles::where('idArticle', $request->idArticle)->first();
        // Mettre à jour l'article
        $article->update($articleValidation);

        // Redirection ou autre traitement
        return response()->json([
            'message' => 'Article mis à jour avec succès',
            'status' => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Articles  $articles
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $idArticle)
    {
        // Valider les données de la requête
        $articleValidation = $request->validate([
            'idCreateur' => ["required", "integer"],
        ]);
        // verifier si l'article existe
        $article = Articles::find($idArticle);
        if (!$article) {
            return response()->json(["message" => "Article non trouvé avec cet id $idArticle"], 404);
        }
        // Vérifier si l'utilisateur est autorisé à supprimer l'article
        if ($article->idCreateur != $articleValidation['idCreateur']) {
            return response()->json(["message" => "Vous n'êtes pas autorisé à supprimer cet article"], 401);
        }

        // Récupérer l'id de l'article dans la requête
        $article = Articles::where('idArticle', $request->idArticle)->first();
        // Supprimer l'article
        $article->delete();

        // Redirection ou autre traitement
        return response()->json([
            'message' => 'Article supprimé avec succès',
            'status' => 200,
        ]);
    }

    // recherche par categorie
    public function searchByCategorie(Request $request)
    {
        $query = Articles::query();
        if ($request->has('categorie')) {
            $categorie = $request->input('categorie');
            if (substr($categorie, -2) == "'s") {
                $categorie = substr($categorie, 0, -2);
            }
            $query->where('categorie', 'like', '%' . $categorie . '%');
        }
        if ($request->has('nomArticle')) {
            $nomArticle = $request->input('nomArticle');
            if (substr($nomArticle, -2) == "'s") {
                $nomArticle = substr($nomArticle, 0, -2);
            }
            $query->where('nomArticle', 'like', '%' . $nomArticle . '%');
        }
        if ($request->has('description')) {
            $description = $request->input('description');
            if (substr($description, -2) == "'s") {
                $description = substr($description, 0, -2);
            }
            $query->where('description', 'like', '%' . $description . '%');
        }
        if ($request->has('couleur')) {
            $couleur = $request->input('couleur');
            if (substr($couleur, -2) == "'s") {
                $couleur = substr($couleur, 0, -2);
            }
            $query->where('couleur', 'like', '%' . $couleur . '%');
        }
        $articles = $query->orderBy('created_at', 'desc')->get();
        return response()->json($articles);
    }

    // filtre
    public function filter(Request $request)
    {
        // Récupérer les paramètres de filtre de la requête
        $taille = $request->input('taille');
        $couleur = $request->input('couleur');
        $prixMin = $request->input('prixMin');
        $prixMax = $request->input('prixMax');
        $categorie = $request->input('categorie');

        // Construire la requête de filtre
        $query = Articles::query();

        if ($taille) {
            $query->whereRaw('LOWER(taille) = ?', [strtolower($taille)]);
        }

        if ($couleur) {
            $query->whereRaw('LOWER(couleur) = ?', [strtolower($couleur)]);
        }

        if ($prixMin) {
            $query->where('prixArticle', '>=', $prixMin);
        }

        if ($prixMax) {
            $query->where('prixArticle', '<=', $prixMax);
        }

        if ($categorie) {
            $query->whereRaw('LOWER(categorie) = ?', [strtolower($categorie)]);
        }

        // Récupérer les articles filtrés
        $articles = $query->get();

        // Retourner les articles filtrés
        return response()->json($articles);
    }
}