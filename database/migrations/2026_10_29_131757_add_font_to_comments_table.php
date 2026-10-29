<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The font the commenter chose for their comment (same fonts as quotes).
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('font')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('font');
        });
    }
};
