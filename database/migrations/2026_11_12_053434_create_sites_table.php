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
    Schema::create('sites', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('url')->unique();
        $table->string('feed_url')->nullable();
        $table->string('district')->nullable();
        $table->string('logo_path')->nullable();
        $table->string('hashtags')->nullable();
        $table->string('site_key', 32)->unique();
        $table->text('secret');
        $table->string('status')->default('active');
        $table->timestamp('trial_ends_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
