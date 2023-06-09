<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'idUser',
        'genre',
        'nom',
        'prenom',
        'date_naissance',
        'mdp_user',
        'email',
        'num_tel',
        'numRue',
        'rue',
        'ville',
        'codePostal',
        'pays',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'mdp_user',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user's idUser.
     *
     * @return int
     */
    protected $primaryKey = 'idUser';

    /**
     * Find the user instance for the given API token.
     * 
     * 
     * @param string $token
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findForPassport($token)
    {
        return $this->where('token', $token)->first();
    }
}
