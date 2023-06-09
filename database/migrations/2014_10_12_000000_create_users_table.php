<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedInteger('idUser')->primary();
            $table->string('genre', 10);
            $table->string('nom', 30);
            $table->string('prenom', 30);
            $table->date('date_naissance');
            $table->string('email', 40)->unique();
            $table->string('mdp_user', 30);
            $table->string('num_tel', 20);
            $table->string('numRue', 255);
            $table->string('rue', 255);
            $table->string('ville', 255);
            $table->string('codePostal', 10);
            $table->string('pays', 100);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
