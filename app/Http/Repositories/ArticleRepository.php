<?php

namespace App\Http\Repositories;

use App\Models\Articles;

class ArticleRepository
{

    // Récupérer tous les articles
    public function getAllArticles()
    {
        $articles = Articles::all();
        return $articles;
    }

    // créer un article
    public function createArticle(array $articleDonnee)
    {
        $article = Articles::create($articleDonnee);
        return $article;
    }

    // mettre à jour un article
    public function updateArticle($idArticle, array $articleValidation, $createurId)
    {
        // Récupérer l'article dans la base de données
        $article = Articles::findOrFail($idArticle);

        // Vérifier si l'utilisateur est autorisé à modifier l'article
        if ($article->idCreateur != $createurId) {
            return response()->json(["message" => "Vous n'êtes pas autorisé à modifier cet article"], 401);
        }

        // Mettre à jour l'article
        $article->update($articleValidation);

        return $article;
    }

    // supprimer un article
    public function deleteArticle($idArticle, $createurId)
    {
        // Récupérer l'article dans la base de données
        $article = Articles::find($idArticle);
        if (!$article) {
            return response()->json(["message" => "Article non trouvé avec cet id $idArticle"], 404);
        }
    
        // Vérifier si l'utilisateur est autorisé à supprimer l'article
        if ($article->idCreateur != $createurId) {
            return response()->json(["message" => "Vous n'êtes pas autorisé à supprimer cet article"], 401);
        }

        // Supprimer l'article
        $article->delete();
    }

    // rechercher un article par son categorie
    public function searchByCategorie($request)
    {
        $query = Articles::query();
        if ($request->filled('categorie')) {
            $categorie = $request->input('categorie');
            if (substr($categorie, -2) == "'s") {
                $categorie = substr($categorie, 0, -2);
            }
            $query->where('categorie', 'like', '%' . $categorie . '%');
        }
        if ($request->filled('nomArticle')) {
            $nomArticle = $request->input('nomArticle');
            if (substr($nomArticle, -2) == "'s") {
                $nomArticle = substr($nomArticle, 0, -2);
            }
            $query->where('nomArticle', 'like', '%' . $nomArticle . '%');
        }
        if ($request->filled('description')) {
            $description = $request->input('description');
            if (substr($description, -2) == "'s") {
                $description = substr($description, 0, -2);
            }
            $query->where('description', 'like', '%' . $description . '%');
        }
        if ($request->filled('couleur')) {
            $couleur = $request->input('couleur');
            if (substr($couleur, -2) == "'s") {
                $couleur = substr($couleur, 0, -2);
            }
            $query->where('couleur', 'like', '%' . $couleur . '%');
        }
        $articles = $query->orderBy('created_at', 'desc')->get();
        return $articles;
    }

    // filtrer les articles par taille, couleur, prix, categorie
    public function filterArticles($taille, $couleur, $prixMin, $prixMax, $categorie)
    {
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

        return $articles;
    }
}
