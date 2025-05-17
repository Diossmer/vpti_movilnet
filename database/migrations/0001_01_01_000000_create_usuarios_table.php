<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        schema::create('estatus', function (Blueprint $table){
            $table->id()->primary();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        schema::create('roles', function (Blueprint $table){
            $table->id()->primary();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('cedula')->unique();
            $table->string('usuario')->unique();
            $table->string('correo')->unique();
            $table->timestamp('correo_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            $table->string('telefono_casa')->nullable();
            $table->string('telefono_celular')->nullable();
            $table->string('telefono_alternativo')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->foreignId('estatus_id')->nullable()->constrained('estatus')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('rol_id')->nullable()->constrained('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('correo')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('estatus');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::enableForeignKeyConstraints();
    }
};
