<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rma', function (Blueprint $table) {
            // Fecha límite para ejercer el derecho de retracto.
            // Se calcula una sola vez al crear el RMA y nunca se modifica.
            // Null si el RMA es de tipo 'standard' o si no aplica retracto.
            $table->timestamp('retracto_expires_at')
                ->nullable()
                ->after('rma_type')
                ->comment('Deadline to exercise withdrawal right (Colombia). Set once at RMA creation.');

            // Flag para productos condicionados (cosméticos/perfumes):
            // el cliente declara explícitamente que el sello de seguridad está intacto.
            $table->boolean('retracto_seal_intact')
                ->nullable()
                ->after('retracto_expires_at')
                ->comment('Customer declaration: sealed condition intact for conditional products (cosmetics/fragrances).');
        });
    }

    public function down(): void
    {
        Schema::table('rma', function (Blueprint $table) {
            $table->dropColumn(['retracto_expires_at', 'retracto_seal_intact']);
        });
    }
};
