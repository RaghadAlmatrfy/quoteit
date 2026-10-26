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
            // Draft or published; a published quote with a future date is "scheduled"
            $table->string('status')->default('published')->index()->after('media_type');
            $table->timestamp('published_at')->nullable()->index()->after('status');

            // How the text looks
            $table->string('font')->nullable()->after('published_at');
            $table->string('font_size')->default('md')->after('font');
            $table->boolean('is_bold')->default(false)->after('font_size');
            $table->boolean('is_italic')->default(false)->after('is_bold');
            $table->string('text_align')->default('start')->after('is_italic');
            $table->string('bg_color', 7)->nullable()->after('text_align');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropColumn(['status', 'published_at', 'font', 'font_size', 'is_bold', 'is_italic', 'text_align', 'bg_color']);
        });
    }
};
