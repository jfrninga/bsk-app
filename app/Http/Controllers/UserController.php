<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserRepository;
use App\Mail\InscriptionUserMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(Request $request)
    {
        // créer un utilisateur
        $userDonnee = $request->validate([
            "genre" => ["required", "string", "max:10"], // "Monsieur" ou "Madame
            "nom" => ["required", "string", "max:30"],
            "prenom" => ["required", "string", "max:30"],
            "date_naissance" => ["required", "date"],
            "email" => ["required", "string", "email", "max:40", "unique:users"],
            "mdp_user" => ["required", "string", "min:8", "max:30"],
            "num_tel" => ["required", "string", "max:20"],
            "numRue" => ["nullable", "string", "max:10"],
            "rue" => ["nullable", "string", "max:50"],
            "codePostal" => ["nullable", "integer", "min:5"],
            "ville" => ["nullable", "string", "max:30"],
            "pays" => ["nullable", "string", "max:30"],
        ]);
        // enregistrer l'utilisateur dans la base de données
        $user = $this->userRepository->createUser($userDonnee);
        // retourner la réponse quand l'utilisateur est créé

        Mail::to($userDonnee["email"])->send(new InscriptionUserMail($userDonnee));

        return response()->json([
            "user" => $user,
            "message" => "Utilisateur créé avec succès",
            "status" => 201,
        ]);
    }

    public function getUserById($idUser)
    {
        // récupérer l'utilisateur par son identifiant
        $user = User::find($idUser);
        // vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                "message" => "Utilisateur non trouvé",
                "status" => 404,
            ]);
        }
        // ne renvoyer que le nom, prénom et la date de naissance de l'utilisateur
        $user = [
            'genre' => $user->genre,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'date_naissance' => $user->date_naissance,
        ];
        // retourner la réponse
        return response()->json([
            "user" => $user,
            "status" => 200,
        ]);
    }

    public function getUsers()
    {
        // récupérer tous les utilisateurs
        $users = User::all();
        // ne renvoyer que le prénom et la date de naissance de chaque utilisateur
        $users = $users->map(function ($user) {
            return [
                'genre' => $user->genre,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'date_naissance' => $user->date_naissance,
            ];
        });
        // retourner la réponse
        return response()->json([
            "users" => $users,
            "status" => 200,
        ]);
    }

    public function loginUser(Request $request)
    {
        // vérifier les données envoyées
        $userDonnee = $request->validate([
            "email" => ["required", "string", "email", "max:40"],
            "mdp_user" => ["required", "string", "min:8", "max:30"],
        ]);

        // vérifier si l'utilisateur existe avec le bon mot de passe
        $user = $this->userRepository->findByEmail($userDonnee["email"]);
        if (!$user || !Hash::check($userDonnee["mdp_user"], $user->mdp_user)) {
            return response()->json([
                "message" => "Email ou mot de passe incorrect",
            ], 401);
        }

        // créer le token
        $tokenResult = $user->createToken('authToken');
        $token = $tokenResult->plainTextToken;

        // retourner la réponse avec le token
        return response()->json([
            "user" => $user,
            "access_token" => $token,
            "message" => "Connexion réussie",
            "status" => 200,
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        // récupérer les données actuelles de l'utilisateur
        $currentDonnee = $user->toArray();

        // vérifier les données envoyées
        $userDonnee = $request->validate([
            "nom" => ["nullable", "string", "max:30"],
            "prenom" => ["nullable", "string", "max:30"],
            "date_naissance" => ["nullable", "date"],
            "email" => ["nullable", "string", "email", "max:40", Rule::unique('users')->ignore($user->id)],
            "mdp_user" => ["nullable", "string", "min:8", "max:30"],
            "num_tel" => ["nullable", "string", "max:20"],
            "numRue" => ["nullable", "string", "max:10"],
            "rue" => ["nullable", "string", "max:50"],
            "codePostal" => ["nullable", "integer", "min:5"],
            "ville" => ["nullable", "string", "max:30"],
            "pays" => ["nullable", "string", "max:30"],
        ]);

        // get the idUser from the url path
        $idUser = $request->route("idUser");
        $userDonnee["idUser"] = intval($idUser);


        if (isset($userDonnee["mdp_user"])) {
            $userDonnee["mdp_user"] = Hash::make($userDonnee["mdp_user"]);
        }

        // fusionner les données validées avec les données actuelles
        $updatedData = array_merge($currentDonnee, array_filter($userDonnee));

        // mettre à jour les données de l'utilisateur via le UserRepository
        $userRepository = new UserRepository();
        $updatedUser = $userRepository->updateUser($updatedData);

        // retourner la réponse quand l'utilisateur est mis à jour
        if ($updatedUser) {
            return response()->json([
                "user" => $updatedUser,
                "message" => "Utilisateur mis à jour avec succès",
            ]);
        } else {
            return response()->json([
                "message" => "Erreur lors de la mise à jour de l'utilisateur",
                "status" => 500,
            ]);
        }
    }

    public function deleteUser(Request $request, $idUser)
    {
        // valider le mot de passe de l'utilisateur
        $request->validate([
            'mdp_user' => ['required', 'string', 'min:8', 'max:30'],
        ]);

        // Appeler la méthode deleteUser du UserRepository
        $result = $this->userRepository->deleteUser($idUser, $request->input('mdp_user'));

        if ($result) {
            return response()->json([
                "message" => "Utilisateur supprimé avec succès",
                "status" => 200,
            ]);
        } else {
            return response()->json([
                "message" => "Mot de passe incorrect",
                "status" => 401,
            ]);
        }
    }

    // public function resetPassword(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'mdp_user' => 'required|confirmed',
    //         'token' => 'required'
    //     ]);

    //     $status = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation', 'token'),
    //         function ($user) use ($request) {
    //             $user->forceFill([
    //                 'password' => Hash::make($request->password)
    //             ])->save();

    //             event(new PasswordReset($user));
    //         }
    //     );

    //     return $status == Password::PASSWORD_RESET
    //         ? response()->json(['message' => 'Password reset successfully.'])
    //         : response()->json(['message' => __($status)]);
    // }

}
