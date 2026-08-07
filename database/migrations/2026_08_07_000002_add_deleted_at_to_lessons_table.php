<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the soft-delete column so trashed lessons can be restored
     * without re-seeding the entire catalog.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
