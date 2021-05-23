<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ap_paterno');
            $table->string('ap_materno')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('unidad');
            $table->bigInteger('rol_id')->unsigned();
            $table->foreign('rol_id')->references('id')->on('rol');
            $table->bigInteger('cargo_id')->unsigned();
            $table->foreign('cargo_id')->references('id')->on('cargo');
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
        Schema::dropIfExists('usuario');
    }
}
