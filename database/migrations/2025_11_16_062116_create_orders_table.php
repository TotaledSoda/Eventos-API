<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Usuario que compra
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Total calculado en base a los ticket_types
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('MXN');

            // Estado: pending | paid | refunded | failed
            $table->string('status')->default('pending');

            // PayPal
            $table->string('paypal_order_id')->nullable()->index();
            $table->string('paypal_capture_id')->nullable()->index();
            $table->json('paypal_payload')->nullable(); // para guardar respuesta cruda si quieres

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Info de error/falla
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
