<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_scans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained()
                ->onDelete('cascade');

            // Usuario staff que escaneó (opcional)
            $table->foreignId('scanned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('scanned_at')->nullable();

            // Resultado: valid | duplicate | invalid | invalidated
            $table->string('result');

            $table->string('device_info')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_scans');
    }
};
