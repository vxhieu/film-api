<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('original_name')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('backdrop_url')->nullable();
            $table->text('description')->nullable();
            $table->integer('total_episodes')->default(1);
            $table->string('current_episode')->nullable();
            $table->string('time')->nullable();
            $table->string('quality')->nullable();
            $table->string('language')->nullable();
            $table->string('year')->nullable();
            $table->string('director')->nullable();
            $table->json('casts')->nullable();
            $table->integer('views')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('films');
    }
}; 