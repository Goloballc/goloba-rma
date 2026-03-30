<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colombian_holidays', function (Blueprint $table) {
            $table->id();

            // Calendar date of the holiday (unique — one row per day)
            $table->date('date')->unique()->comment('Holiday date (Colombia).');

            // Human-readable name in Spanish
            $table->string('name', 120)->comment('Holiday name in Spanish.');

            // Derived year column for fast yearly queries (WHERE year = ?)
            $table->unsignedSmallInteger('year')
                ->comment('Year, redundant with date but used for indexed lookups.');

            $table->timestamps();

            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colombian_holidays');
    }
};
