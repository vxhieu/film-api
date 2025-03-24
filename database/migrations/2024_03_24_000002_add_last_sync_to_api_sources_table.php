<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('api_sources', function (Blueprint $table) {
            $table->timestamp('last_sync')->nullable()->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('api_sources', function (Blueprint $table) {
            $table->dropColumn('last_sync');
        });
    }
}; 