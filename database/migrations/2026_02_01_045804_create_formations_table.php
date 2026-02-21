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
        Schema::create('formations', function (Blueprint $table) {
            $table->id('CodeFormation');
            
            // Fifandraisana amin'ny table membres
            // Mampiasa unsignedBigInteger satria ny primary key matetika dia izany
            $table->unsignedBigInteger('NumMembre');
            $table->unsignedBigInteger('user_id');
    
            // Ireo "Foreign Keys" miaraka amin'ny CASCADE
            $table->foreign('NumMembre')
                  ->references('NumMembre')
                  ->on('membres')
                  ->onDelete('cascade') // Fafana ny formation raha mamafa membre
                  ->onUpdate('cascade'); // Ovaina ny NumMembre raha miova any amin'ny membres
    
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
    
            // Ireo kolom-piofanana (Checkbox any amin'ny React)
            $table->boolean('GestionSimplifiee')->default(false);
            $table->boolean('AgroSol')->default(false);
            $table->boolean('AgroEco')->default(false);
            $table->boolean('AgroEau')->default(false);
            $table->boolean('AgroVegetaux')->default(false);
            $table->boolean('ProductionSemence')->default(false);
            $table->boolean('Nutrition')->default(false);
            $table->boolean('NutritionEau')->default(false);
            $table->boolean('NutritionAlimentaire')->default(false);
            $table->boolean('ConservationProduit')->default(false);
            $table->boolean('TransformationProduit')->default(false);
            $table->boolean('Genre')->default(false);
            $table->boolean('EPRACC')->default(false);
    
            $table->text('Autre')->nullable();
            $table->string('Autonomie')->default('Non');
            
            $table->timestamps(); // create_at sy updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
