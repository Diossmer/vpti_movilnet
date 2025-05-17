<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad_existente')->nullable();
            $table->integer('entrada')->nullable();
            $table->integer('salida')->nullable();
            $table->text('descripcion')->nullable();
            $table->foreignId('estatus_id')->nullable()->constrained('estatus')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('perifericos', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad_existente')->nullable();
            $table->integer('entrada')->nullable();
            $table->integer('salida')->nullable();
            $table->text('descripcion')->nullable();
            $table->foreignId('estatus_id')->nullable()->constrained('estatus')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('estatus_id')->nullable()->constrained('estatus')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('descripcion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('modelo')->nullable();
            $table->string('dispositivo')->nullable();
            $table->string('serial')->nullable();
            $table->string('marca')->nullable();
            $table->text('observacion')->nullable()->unique();
            $table->string('codigo_inv')->nullable();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('ubicacion', function (Blueprint $table) {
            $table->id();
            $table->string('origen')->nullable();
            $table->string('destino')->nullable();
            $table->string('piso')->nullable();
            $table->string('region')->nullable();
            $table->string('estado')->nullable();
            $table->string('capital')->nullable();
            $table->foreignId('descripcion_id')->unique()->nullable()->constrained('descripcion')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->string('estado_fisico')->nullable();
            $table->string('escala')->nullable();
            $table->string('compatibilidad')->nullable();
            $table->string('reemplazo')->nullable();
            $table->string('mantenimineto')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('estatus_id')->nullable()->constrained('estatus')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('descripcion_id')->unique()->nullable()->constrained('descripcion')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('asignacion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_asignar')->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->string('destino')->nullable();
            $table->text('comentario')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('estatus_id')->nullable()->constrained('estatus')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('descripcion_id')->unique()->nullable()->constrained('descripcion')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inventarios');
        Schema::dropIfExists('perifericos');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('descripcion');
        Schema::dropIfExists('ubicacion');
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('asignacion');
        Schema::enableForeignKeyConstraints();
    }
};
