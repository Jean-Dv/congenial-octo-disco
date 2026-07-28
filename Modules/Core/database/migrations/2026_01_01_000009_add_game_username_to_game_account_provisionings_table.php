<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_account_provisionings', function (Blueprint $table) {
            $table->string('game_username', 16)->nullable()->after('realm_id');
        });
    }

    public function down(): void
    {
        Schema::table('game_account_provisionings', function (Blueprint $table) {
            $table->dropColumn('game_username');
        });
    }
};
