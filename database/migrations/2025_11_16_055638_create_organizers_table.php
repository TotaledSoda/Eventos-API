<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();

            // Usuario dueño de este organizador
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Datos básicos de organizador
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();

            // Verificación
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizers');
    }
};
