<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->boolean('offline')->default(false)->after('granted_minutes');
            $table->index(['offline','created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropIndex(['offline','created_at']);
            $table->dropColumn('offline');
        });
    }
};
