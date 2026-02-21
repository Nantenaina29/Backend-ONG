<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('responsables', function (Blueprint $table) {
            $table->id('CodeRespo'); 
            $table->integer('NumMembre')->unique(); // Integer mifanaraka amin'ny Membres
            $table->string('Poste')->nullable();    // Ity ihany no fenoina avy eo
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->timestamps();
    
            // Fifandraisana amin'ny table membres
            $table->foreign('NumMembre')->references('NumMembre')->on('membres')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsables');
    }
};
