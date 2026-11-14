<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('cards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('article_id')->unique()->constrained()->cascadeOnDelete();
        $table->foreignId('site_id')->constrained()->cascadeOnDelete();
        $table->string('design');
        $table->string('path');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
