<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ArticleRepository;
use App\Models\Articles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticlesController extends Controller
{

    private $articlesRepository;

    public function __construct(ArticleRepository $articlesRepository)
    {
        $this->articlesRepository = $articlesRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = $this->articlesRepository->getAllArticles();
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

        // Créer un nouvel article en utilisant le ArticleRepository
        $article = $this->articlesRepository->createArticle($articleDonnee);

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
        $articleDonnee = [];

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

        // Récupérer l'article dans la base de données
        $article = app(ArticleRepository::class)->updateArticle($idArticle, $articleValidation, $request->createur()->id);

        // Vérifier si l'utilisateur est autorisé à modifier l'article
        if ($article->idCreateur != $request->createur()->id) {
            return response()->json(["message" => "Vous n'êtes pas autorisé à modifier cet article"], 401);
        }

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

        // supprimer l'article à partir de ArticleRepository
        app(ArticleRepository::class)->deleteArticle($idArticle);

        // Redirection ou autre traitement
        return response()->json([
            'message' => 'Article supprimé avec succès',
            'status' => 200,
        ]);
    }

    // recherche par categorie
    public function searchByCategorie(Request $request)
    {
        $articles = $this->articlesRepository->searchByCategorie($request);
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

        // Appeler la méthode du repository pour effectuer le filtrage
        $filteredArticles = app(ArticleRepository::class)->filterArticles($taille, $couleur, $prixMin, $prixMax, $categorie);

        // Retourner les articles filtrés
        return response()->json($filteredArticles);
    }
}
