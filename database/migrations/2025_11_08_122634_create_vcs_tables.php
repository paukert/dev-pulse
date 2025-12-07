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
        Schema::create('vcs_instances', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('api_url');
            $table->text('token')->nullable();
            $table->enum('platform', ['github', 'gitlab']);
        });

        Schema::create('vcs_instance_users', static function (Blueprint $table): void {
            $table->id();
            $table->string('vcs_id');
            $table->string('username');
            $table->foreignId('vcs_instance_id')->constrained('vcs_instances')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('repositories', static function (Blueprint $table): void {
            $table->id();
            $table->string('vcs_id');
            $table->string('name');
            $table->unsignedInteger('sync_interval');
            $table->timestamp('statistics_from');
            $table->timestamp('last_synced_at')->nullable();
            $table->foreignId('vcs_instance_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('pull_requests', static function (Blueprint $table): void {
            $table->id();
            $table->string('vcs_id');
            $table->string('title');
            $table->enum('state', ['open', 'closed', 'merged']);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('merged_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('repository_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('vcs_instance_users', 'id');
            $table->foreignId('merged_by_user_id')->nullable()->constrained('vcs_instance_users', 'id');
            $table->foreignId('closed_by_user_id')->nullable()->constrained('vcs_instance_users', 'id');
        });

        Schema::create('pull_request_metrics', static function (Blueprint $table): void {
            $table->unsignedInteger('added_lines');
            $table->unsignedInteger('deleted_lines');
            $table->unsignedInteger('files_count');
            $table->foreignId('pull_request_id')->primary()->constrained()->cascadeOnDelete();
        });

        Schema::create('approvers', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('approved_at');
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vcs_instance_user_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('reviewers', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('assigned_at');
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vcs_instance_user_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('threads', static function (Blueprint $table): void {
            $table->id();
            $table->string('vcs_id');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('vcs_instance_users', 'id')->cascadeOnDelete();
        });

        Schema::create('comments', static function (Blueprint $table): void {
            $table->id();
            $table->string('vcs_id');
            $table->text('text');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vcs_instance_user_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('vcs_instance_users');
        Schema::dropIfExists('vcs_instances');
    }
};
