<?php


namespace App\Http\Repositories;

use App\Models\Createur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateurRepository
{

    // créer un createur
    public function createCreateur(array $createurDonnee)
    {
        // Hasher le mot de passe
        $createurDonnee["mdp_createur"] = Hash::make($createurDonnee["mdp_createur"]);

        // Enregistrer le createur dans la base de données
        $createur = Createur::create($createurDonnee);

        return $createur;
    }

    // recuperer tous les createurs
    public function getAllCreateurs()
    {
        $createurs = Createur::all();
        // ne renvoyer que le prénom et la date de naissance de chaque createur
        $createurs = $createurs->map(function ($createur) {
            return [
                'genre' => $createur->genre,
                'nom' => $createur->nom,
                'prenom' => $createur->prenom,
                'email' => $createur->email,
            ];
        });
        return $createurs;
    }

    // Récupérer un createur par son identifiant quand il se connecte
    public function findByEmail($email)
    {
        return Createur::where('email', $email)->first();
    }

    // pour mettre à jour les données du createur
    public function updateCreateur($idCreateur, $updatedData)
    {
        return DB::table('createurs')
            ->where('idCreateur', $idCreateur)
            ->update([
                'nom' => $updatedData['nom'],
                'prenom' => $updatedData['prenom'],
                'dateNaissance' => $updatedData['dateNaissance'],
                'email' => $updatedData['email'],
                'mdpCreateur' => $updatedData['mdpCreateur'],
                'telCreateur' => $updatedData['telCreateur'],
                'numRue' => $updatedData['numRue'],
                'rue' => $updatedData['rue'],
                'codePostal' => $updatedData['codePostal'],
                'ville' => $updatedData['ville'],
                'pays' => $updatedData['pays'],
                'debutActivite' => $updatedData['debutActivite'],
                'siret' => $updatedData['siret'],
            ]);
    }

    // Supprimer un createur
    public function deleteCreateur($idCreateur, $mdpCreateur)
    {
        $createur = Createur::findOrFail($idCreateur);
        if (!Hash::check($mdpCreateur, $createur->mdpCreateur)) {
            return response()->json([
                'message' => 'Mot de passe incorrect',
            ], 401);
        }

        // supprimer les tokens du createur
        $createur->tokens()->delete();
        $createur->delete();

        return true;
    }
}
