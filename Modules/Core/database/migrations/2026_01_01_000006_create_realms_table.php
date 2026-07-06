<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('core_type', 30);

            // Cifrados en reposo (cast "encrypted:array" en RealmModel):
            // credenciales de BD y de SOAP de cada reino. text(), no
            // string(), porque el valor cifrado es mucho mas largo.
            $table->text('auth_database');
            $table->text('characters_database')->nullable();
            $table->text('remote_console');

            $table->integer('gm_realm_id')->default(-1);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realms');
    }
};
