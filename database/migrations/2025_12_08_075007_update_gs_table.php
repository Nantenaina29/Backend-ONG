<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gs', function (Blueprint $table) {
            $table->bigIncrements('CodeGS'); // PRIMARY KEY
            $table->string('nom'); // anaran’ny GS

            // numMenage maromaro → VARCHAR + nullable
            $table->string('numMenage')->nullable();

            // ho automatique avy amin'ny backend (count membres)
            $table->integer('effectif')->default(0);

            $table->date('dateCreation');
            $table->string('commune');
            $table->string('fokontany');
            $table->string('village');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gs');
    }
};
