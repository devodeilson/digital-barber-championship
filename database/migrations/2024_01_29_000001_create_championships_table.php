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
            $table->text('description')->nullable();
            $table->decimal('entry_fee', 10, 2);
            $table->string('pix_key');
            $table->datetime('registration_start');
            $table->datetime('registration_end');
            $table->datetime('voting_start');
            $table->datetime('voting_end');
            $table->enum('status', ['draft', 'active', 'voting', 'finished'])->default('draft');
            $table->string('image')->nullable();
            $table->boolean('is_final_cup')->default(false);
            $table->timestamps();
        });

        Schema::create('championship_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('championship_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending_payment', 'paid', 'confirmed'])->default('pending_payment');
            $table->string('payment_id')->nullable();
            $table->datetime('payment_date')->nullable();
            $table->timestamps();
            $table->unique(['championship_id', 'user_id']);
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('championship_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('youtube_id');
            $table->string('original_filename');
            $table->integer('duration_seconds');
            $table->enum('status', ['processing', 'published', 'failed'])->default('processing');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating');
            $table->timestamps();
            $table->unique(['video_id', 'user_id']);
        });

        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->decimal('score', 10, 2);
            $table->enum('type', ['daily', 'general', 'final'])->default('daily');
            $table->date('ranking_date');
            $table->timestamps();
            $table->unique(['championship_id', 'user_id', 'type', 'ranking_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('championship_participants');
        Schema::dropIfExists('championship_categories');
        Schema::dropIfExists('championships');
    }
};
