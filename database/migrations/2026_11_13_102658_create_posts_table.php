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
        $table->string('icon_path')->nullable()->after('logo_path');
        $table->boolean('card_show_category')->default(true);
        $table->boolean('card_show_url')->default(true);
        $table->string('card_photo_tag', 40)->nullable();
    });
}

public function down(): void
{
    Schema::table('sites', function (Blueprint $table) {
        $table->dropColumn(['icon_path', 'card_show_category', 'card_show_url', 'card_photo_tag']);
    });
}
};
