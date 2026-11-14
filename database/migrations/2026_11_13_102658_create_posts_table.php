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
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('site_id')->constrained()->cascadeOnDelete();
        $table->foreignId('card_id')->constrained()->cascadeOnDelete();
        $table->string('destination', 20);
        $table->string('status', 20)->default('pending');
        $table->unsignedTinyInteger('attempts')->default(0);
        $table->string('remote_id')->nullable();
        $table->string('comment_id')->nullable();
        $table->text('error')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();

        $table->unique(['card_id', 'destination']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
