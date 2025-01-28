<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('championships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['league', 'normal', 'final_cup'])->default('league');
            $table->enum('status', ['draft', 'active', 'voting', 'finished', 'cancelled'])->default('draft');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->string('image')->nullable();
            $table->integer('max_participants');
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela de classificação
        Schema::create('championship_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->integer('points');
            $table->boolean('qualified_for_final')->default(false);
            $table->timestamps();
        });

        // Tabela de rankings diários
        Schema::create('daily_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points');
            $table->date('ranking_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_rankings');
        Schema::dropIfExists('championship_rankings');
        Schema::dropIfExists('championships');
    }
};
