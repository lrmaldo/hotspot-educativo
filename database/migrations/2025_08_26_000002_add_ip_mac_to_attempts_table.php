<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->string('ip', 45)->nullable()->after('identifier');
            $table->string('mac', 32)->nullable()->after('ip');
            $table->index(['mac','created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropIndex(['mac','created_at']);
            $table->dropColumn(['ip','mac']);
        });
    }
};
