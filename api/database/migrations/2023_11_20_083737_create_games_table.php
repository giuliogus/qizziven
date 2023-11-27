<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->integer('winned_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->integer('match_id')->unsigned();
            $table->text('question');
            $table->boolean('active')->default(true);
            $table->boolean('over')->default(false);
            $table->integer('winned_by')->unsigned()->nullable();
            $table->integer('created_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id')->unsigned();
            $table->text('text')->nullable();
            $table->boolean('correct')->nullable();
            $table->integer('created_by')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
        Schema::dropIfExists('games');
        Schema::dropIfExists('answers');
    }
};
