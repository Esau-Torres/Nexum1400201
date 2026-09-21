<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id('docente_id');
            $table->foreignId('id_usuario')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('codigo_empleado', 11)->unique();
            $table->string('correo_institucional', 255)->unique();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};