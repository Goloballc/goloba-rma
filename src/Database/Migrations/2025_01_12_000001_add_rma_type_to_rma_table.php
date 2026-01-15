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
        Schema::table('rma', function (Blueprint $table) {
            $table->enum('rma_type', ['standard', 'retracto'])
                ->default('standard')
                ->after('rma_status')
                ->comment('Tipo de RMA: standard o derecho de retracto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rma', function (Blueprint $table) {
            $table->dropColumn('rma_type');
        });
    }
};
