<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateReseauxTable extends Migration
{
public function up()
{
Schema::create('reseaux', function (Blueprint $table) {
$table->id('CodeRS'); // primary key auto-increment
$table->string('NomRS');
$table->string('NomGS')->nullable(); // multiple GS, separé par virgule
$table->date('DateCreation')->nullable();
$table->boolean('Activite')->default(false);
$table->boolean('Plaidoyer')->default(false);
$table->boolean('Plan')->default(false);
$table->string('Autonomie')->default('Non'); // Non ou Autonome
$table->timestamps();
});
}


public function down()
{
Schema::dropIfExists('reseaux');
}
}