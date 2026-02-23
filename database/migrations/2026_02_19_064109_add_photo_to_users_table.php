<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Esorina ilay ->change() satria vao hamorona isika fa tsy hanova
            $table->text('photo')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tsara raha fafana ilay column raha sendra manao rollback
            $table->dropColumn('photo');
        });
    }
};