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
    Schema::create('connections', function (Blueprint $table) {
        $table->id();
        $table->foreignId('site_id')->constrained()->cascadeOnDelete();
        $table->string('provider', 20);
        $table->string('external_id');
        $table->string('name');
        $table->text('token');
        $table->string('status', 20)->default('active');
        $table->timestamps();

        $table->unique(['site_id', 'provider']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
