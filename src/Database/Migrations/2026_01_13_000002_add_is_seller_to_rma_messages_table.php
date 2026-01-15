<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rma_messages', function (Blueprint $table) {
            // Agregar campo para identificar si el mensaje es del vendedor
            $table->boolean('is_seller')->default(0)->after('is_admin');
            
            // Agregar índice para mejorar queries
            $table->index(['rma_id', 'is_admin', 'is_seller']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rma_messages', function (Blueprint $table) {
            $table->dropIndex(['rma_id', 'is_admin', 'is_seller']);
            $table->dropColumn('is_seller');
        });
    }
};
