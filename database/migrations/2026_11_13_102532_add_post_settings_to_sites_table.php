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
    Schema::table('sites', function (Blueprint $table) {
        $table->string('caption_mode', 20)->default('title_hashtags')->after('hashtags');
        $table->text('caption_template')->nullable()->after('caption_mode');
        $table->boolean('post_facebook')->default(true)->after('caption_template');
        $table->boolean('post_telegram')->default(false)->after('post_facebook');
        $table->text('telegram_token')->nullable()->after('post_telegram');
        $table->string('telegram_chat', 100)->nullable()->after('telegram_token');
    });
}

public function down(): void
{
    Schema::table('sites', function (Blueprint $table) {
        $table->dropColumn([
            'caption_mode', 'caption_template', 'post_facebook',
            'post_telegram', 'telegram_token', 'telegram_chat',
        ]);
    });
}
};
