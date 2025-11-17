<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');             // General, VIP, etc.
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);    // precio del boleto
            $table->unsignedInteger('stock');   // stock total disponible

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
