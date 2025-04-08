<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('release_date');
            $table->integer('runtime_minutes')->nullable();
            $table->string('language')->nullable();
            $table->string('poster')->nullable();
            $table->string('trailer_url')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('revenue', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movies');
    }
}; 