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
    Schema::create('custom_designs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('site_id')->constrained()->cascadeOnDelete();
        $table->string('name', 60);
        $table->json('settings');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_designs');
    }
};
