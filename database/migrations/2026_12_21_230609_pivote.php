<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->nullable()->constrained('inventarios')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('perifericos_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periferico_id')->nullable()->constrained('perifericos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('asignados_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignar_id')->nullable()->constrained('asignacion')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inventarios_productos');
        Schema::dropIfExists('perifericos_productos');
        Schema::dropIfExists('asignados_productos');
        Schema::enableForeignKeyConstraints();
    }
};
