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
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('field')->default('ai')->index()->after('content');
            $table->string('media_path')->nullable()->after('field');
            $table->string('media_type')->nullable()->after('media_path'); // image | video
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['field']);
            $table->dropColumn(['field', 'media_path', 'media_type']);
        });
    }
};
