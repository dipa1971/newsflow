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
        $table->string('design')->default('design1')->after('hashtags');
        $table->timestamp('last_checked_at')->nullable();
        $table->timestamp('baseline_done_at')->nullable();
        $table->text('last_error')->nullable();
    });
}

public function down(): void
{
    Schema::table('sites', function (Blueprint $table) {
        $table->dropColumn(['design', 'last_checked_at', 'baseline_done_at', 'last_error']);
    });
}
};
