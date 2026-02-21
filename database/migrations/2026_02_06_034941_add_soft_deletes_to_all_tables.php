<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        // Ho an'ny tabilao membres
        Schema::table('membres', function (Blueprint $table) {
            $table->softDeletes(); // Manampy 'deleted_at'
        });
        Schema::table('gs', function (Blueprint $table) {
            $table->softDeletes(); // Manampy 'deleted_at'
        });

        // Ho an'ny tabilao reseaux
        Schema::table('reseaux', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('responsables', function (Blueprint $table) {
            $table->softDeletes(); // Manampy 'deleted_at'
        });
        // Ho an'ny tabilao formations
        Schema::table('formations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        // Fanafoanana azy raha misy rollback
        Schema::table('membres', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('gs', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('reseaux', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('responsables', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('formations', function (Blueprint $table) { $table->dropSoftDeletes(); });
        
    }
};