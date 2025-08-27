<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('attempts')) return;
        Schema::table('attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('attempts','attempt_order')) {
                $table->unsignedInteger('attempt_order')->default(1)->after('connection_error');
            }
        });
    }
    public function down(): void
    {
        if (!Schema::hasTable('attempts')) return;
        Schema::table('attempts', function (Blueprint $table) {
            if (Schema::hasColumn('attempts','attempt_order')) {
                $table->dropColumn('attempt_order');
            }
        });
    }
};
