<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('router_devices', function (Blueprint $table) {
            $table->boolean('ssl')->default(false)->after('port');
            $table->unsignedSmallInteger('timeout')->default(5)->after('ssl');
        });
    }
    public function down(): void
    {
        Schema::table('router_devices', function (Blueprint $table) {
            $table->dropColumn(['ssl','timeout']);
        });
    }
};
