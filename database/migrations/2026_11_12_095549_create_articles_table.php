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
    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('site_id')->constrained()->cascadeOnDelete();
        $table->char('guid_hash', 64);
        $table->string('title', 500);
        $table->text('link');
        $table->text('excerpt')->nullable();
        $table->string('category', 100)->nullable();
        $table->text('image_url')->nullable();
        $table->string('image_path')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->string('status')->default('pending');
        $table->string('skip_reason')->nullable();
        $table->timestamps();

        $table->unique(['site_id', 'guid_hash']);
    });
}




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
