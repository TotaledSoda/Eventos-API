<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Organizador dueño del evento
            $table->foreignId('organizer_id')
                ->constrained()
                ->onDelete('cascade');

            // Información básica
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();    // ej: "Concierto", "Conferencia"
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();  // ruta de imagen del evento

            // Fecha y hora
            $table->dateTime('starts_at');

            // Ubicación básica + mapa
            $table->string('venue_name')->nullable();  // Nombre del lugar (ej. "Arena GDL")
            $table->string('address')->nullable();     // Dirección texto
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Estado del evento
            // draft | published | canceled
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
