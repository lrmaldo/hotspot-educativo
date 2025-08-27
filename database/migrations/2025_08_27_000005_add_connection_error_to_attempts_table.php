<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('attempts')) return;
        Schema::table('attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('attempts','connection_error')) {
                $table->text('connection_error')->nullable()->after('offline');
            }
        });
    }
    public function down(): void
    {
        if (!Schema::hasTable('attempts')) return;
        Schema::table('attempts', function (Blueprint $table) {
            if (Schema::hasColumn('attempts','connection_error')) {
                $table->dropColumn('connection_error');
            }
        });
    }
};
