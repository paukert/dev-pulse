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
        Schema::create('platforms', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('vcs_instances', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('api_url');
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('repositories', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('access_token');
            $table->timestamp('statistics_from');
            $table->foreignId('vcs_instance_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('pull_requests', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('state');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('merged_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('repository_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users', 'id');
            $table->foreignId('merged_by_user_id')->nullable()->constrained('users', 'id');
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users', 'id');
        });

        Schema::create('pull_request_metrics', static function (Blueprint $table): void {
            $table->unsignedInteger('added_lines');
            $table->unsignedInteger('deleted_lines');
            $table->unsignedInteger('files_count');
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('approvers', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('approved_at');
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('reviewers', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('assigned_at');
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('threads', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users', 'id')->cascadeOnDelete();
        });

        Schema::create('comments', static function (Blueprint $table): void {
            $table->id();
            $table->text('text');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('thread_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('threads');
        Schema::dropIfExists('reviewers');
        Schema::dropIfExists('approvers');
        Schema::dropIfExists('pull_request_metrics');
        Schema::dropIfExists('pull_requests');
        Schema::dropIfExists('repositories');
        Schema::dropIfExists('vcs_instances');
        Schema::dropIfExists('platforms');
    }
};
