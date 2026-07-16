<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gr_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('gr_abilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('gr_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('gr_teams')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->unique(['team_id', 'slug']);
        });

        Schema::create('gr_ability_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('gr_roles')->cascadeOnDelete();
            $table->foreignId('ability_id')->constrained('gr_abilities')->cascadeOnDelete();
            $table->primary(['role_id', 'ability_id']);
        });

        Schema::create('gr_model_roles', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignId('role_id')->constrained('gr_roles')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('gr_teams')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['model_type', 'model_id', 'role_id', 'team_id'], 'gr_model_roles_unique');
        });

        Schema::create('gr_impersonation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_user_id');
            $table->unsignedBigInteger('target_user_id');
            $table->foreignId('team_id')->nullable()->constrained('gr_teams')->nullOnDelete();
            $table->string('reason');
            $table->timestamp('started_at');
            $table->timestamp('ends_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gr_impersonation_logs');
        Schema::dropIfExists('gr_model_roles');
        Schema::dropIfExists('gr_ability_role');
        Schema::dropIfExists('gr_roles');
        Schema::dropIfExists('gr_abilities');
        Schema::dropIfExists('gr_teams');
    }
};
