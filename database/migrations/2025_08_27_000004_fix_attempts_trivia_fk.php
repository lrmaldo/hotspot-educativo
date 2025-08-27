<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('attempts') || !Schema::hasTable('trivias')) return;

        // Detectar si la FK ya está bien (en SQLite es más simple: intentamos un pragma)
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if ($driver === 'sqlite') {
            // Si estamos en un migrate:fresh, la tabla se creó recién con la FK correcta, no hacemos nada.
            // Comprobamos si el esquema ya referencia 'trivias'
            $schema = collect(DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='attempts'"))->pluck('sql')->first();
            if ($schema && str_contains($schema, 'references "trivias"')) {
                return; // ya correcto
            }
            // Si no contiene la referencia, recreamos sólo si hay datos que preservar.
            $count = 0;
            try { $count = (int) DB::table('attempts')->count(); } catch(\Throwable $e) { /* ignore */ }
            if ($count === 0) {
                // Más simple: dropear y recrear según migración original corregida.
                Schema::drop('attempts');
                Schema::create('attempts', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('trivia_id')->constrained('trivias')->cascadeOnDelete();
                    $table->string('identifier')->index();
                    $table->string('ip',45)->nullable();
                    $table->string('mac',32)->nullable();
                    $table->enum('selected_option',['A','B','C','D']);
                    $table->boolean('is_correct');
                    $table->string('mikrotik_username')->nullable();
                    $table->string('mikrotik_password')->nullable();
                    $table->unsignedInteger('granted_minutes')->default(0);
                    $table->boolean('offline')->default(false);
                    $table->timestamps();
                    $table->index(['identifier','created_at']);
                    $table->index(['mac','created_at']);
                    $table->index(['offline','created_at']);
                });
                return;
            }
            // Hay datos: recreación con copia (evitar duplicar índices si se re-ejecuta)
            DB::statement('BEGIN TRANSACTION');
            DB::statement('CREATE TABLE attempts_tmp (
                id integer primary key autoincrement not null,
                trivia_id integer not null references trivias(id) on delete cascade,
                identifier varchar not null,
                ip varchar(45) null,
                mac varchar(32) null,
                selected_option varchar not null,
                is_correct integer not null,
                mikrotik_username varchar null,
                mikrotik_password varchar null,
                granted_minutes integer not null default 0,
                offline integer not null default 0,
                created_at datetime null,
                updated_at datetime null
            )');
            // Crear índices sólo si no existen (en tabla nueva no existen aún)
            DB::statement('CREATE INDEX attempts_identifier_created_at_index ON attempts_tmp(identifier, created_at)');
            DB::statement('CREATE INDEX attempts_mac_created_at_index ON attempts_tmp(mac, created_at)');
            DB::statement('CREATE INDEX attempts_offline_created_at_index ON attempts_tmp(offline, created_at)');
            try { DB::statement('INSERT INTO attempts_tmp (id,trivia_id,identifier,ip,mac,selected_option,is_correct,mikrotik_username,mikrotik_password,granted_minutes,offline,created_at,updated_at) SELECT id,trivia_id,identifier,ip,mac,selected_option,is_correct,mikrotik_username,mikrotik_password,granted_minutes,COALESCE(offline,0),created_at,updated_at FROM attempts'); } catch (\Throwable $e) { /* ignore copy */ }
            DB::statement('DROP TABLE attempts');
            DB::statement('ALTER TABLE attempts_tmp RENAME TO attempts');
            DB::statement('COMMIT');
        } else {
            // Para otros motores: intentar soltar FK incorrecta y crear la correcta.
            // Identificar nombre de la FK (depende del motor, aquí suponemos convención laravel)
            Schema::table('attempts', function (Blueprint $table) {
                // Intentamos primero dropear cualquier FK previa si existiera.
                try { $table->dropForeign(['trivia_id']); } catch (\Throwable $e) {}
                $table->foreign('trivia_id')->references('id')->on('trivias')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // No revertimos; dejar FK correcta.
    }
};
