<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    // Créer un utilisateur
    public function createUser(array $userDonnee)
    {
        // Hasher le mot de passe
        $userDonnee["mdp_user"] = Hash::make($userDonnee["mdp_user"]);

        // Enregistrer l'utilisateur dans la base de données
        $user = User::create($userDonnee);

        return $user;
    }

    // Récupérer un utilisateur par son identifiant quand il se connecte
    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    // pour mettre à jour les données de l'utilisateur
    public function updateUser(array $updatedData)
    {
        // mettre à jour les données de l'utilisateur
        $sql = "UPDATE users SET nom=:nom, prenom=:prenom, date_naissance=:date_naissance, email=:email,
                    mdp_user=:mdp_user, num_tel=:num_tel, numRue=:numRue, rue=:rue, codePostal=:codePostal,
                    ville=:ville, pays=:pays WHERE idUser=:idUser";
        $updatedDonnee = [
            "nom" => $updatedData["nom"],
            "prenom" => $updatedData["prenom"],
            "date_naissance" => $updatedData["date_naissance"],
            "email" => $updatedData["email"],
            "mdp_user" => $updatedData["mdp_user"],
            "num_tel" => $updatedData["num_tel"],
            "numRue" => isset($updatedData["numRue"]) ? $updatedData["numRue"] : null,
            "rue" => isset($updatedData["rue"]) ? $updatedData["rue"] : null,
            "codePostal" => isset($updatedData["codePostal"]) ? $updatedData["codePostal"] : null,
            "ville" => isset($updatedData["ville"]) ? $updatedData["ville"] : null,
            "pays" => isset($updatedData["pays"]) ? $updatedData["pays"] : null,
            "idUser" => $updatedData["idUser"],
        ];
        $result = DB::statement($sql, $updatedDonnee);

        // retourner les données mises à jour de l'utilisateur
        if ($result) {
            return $updatedData;
        } else {
            return null;
        }
    }

    // Supprimer un utilisateur
    public function deleteUser($idUser, $mdp_user)
    {
        // Récupérer l'utilisateur par ID
        $user = User::findOrFail($idUser);

        // Vérifier si le mot de passe est correct
        if (!Hash::check($mdp_user, $user->mdp_user)) {
            return false;
        }

        // Retirer le token associé à l'utilisateur dans la base de données
        $user->tokens()->delete();

        // Supprimer l'utilisateur
        $user->delete();

        return true;
    }
}
