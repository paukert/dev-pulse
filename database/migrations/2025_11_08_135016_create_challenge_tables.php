<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('challenges', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('active_from');
            $table->timestamp('active_to');
        });

        Schema::create('challenge_activity_types', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('challenge_activities', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('needed_actions_count');
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_activity_type_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('badges', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('earned_at');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
        Schema::dropIfExists('challenge_activities');
        Schema::dropIfExists('challenge_activity_types');
        Schema::dropIfExists('challenges');
    }
};
