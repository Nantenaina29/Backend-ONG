<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membres', function (Blueprint $table) {
            $table->id('NumMembre');
            $table->string('NomMembre');
            $table->string('PrenomMembre');
            $table->integer('AnneeNaissance');
            $table->string('Sexe', 2);
            $table->string('Chef');
            $table->integer('NumMenage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membres');
    }
};
