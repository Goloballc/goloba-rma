<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea las tablas para el mecanismo de disputas de RMA.
 *
 * Flujo:
 *   - El vendedor nunca puede rechazar una RMA directamente.
 *   - En su lugar abre una disputa (rma_status → 'Disputed') con observaciones
 *     y evidencias fotográficas.
 *   - El admin decide: aprobar la disputa (→ Declined) o rechazarla (→ Accept).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rma_disputes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rma_id');   // rma.id es int unsigned
            $table->unsignedBigInteger('seller_id');
            $table->text('observations');
            $table->enum('admin_resolution', ['approved', 'rejected'])->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('rma_id')->references('id')->on('rma')->onDelete('cascade');
        });

        Schema::create('rma_dispute_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dispute_id');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->foreign('dispute_id')->references('id')->on('rma_disputes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rma_dispute_images');
        Schema::dropIfExists('rma_disputes');
    }
};
