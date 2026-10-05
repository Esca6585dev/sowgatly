<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables for spatie/laravel-permission (roles and permissions of admins).
 * The package was installed without its migration, so the Admins, Roles and
 * Permissions pages failed with "no such table: roles". Each table is only
 * created when it does not exist yet, so databases that already have them
 * (created by hand from the package stub) are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tables = config('permission.table_names');
        $columns = config('permission.column_names');
        $morphKey = $columns['model_morph_key'] ?? 'model_id';

        if (! Schema::hasTable($tables['permissions'])) {
            Schema::create($tables['permissions'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable($tables['roles'])) {
            Schema::create($tables['roles'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable($tables['model_has_permissions'])) {
            Schema::create($tables['model_has_permissions'], function (Blueprint $table) use ($tables, $morphKey) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger($morphKey);
                $table->index([$morphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->foreign('permission_id')->references('id')->on($tables['permissions'])->onDelete('cascade');
                $table->primary(['permission_id', $morphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
        }

        if (! Schema::hasTable($tables['model_has_roles'])) {
            Schema::create($tables['model_has_roles'], function (Blueprint $table) use ($tables, $morphKey) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger($morphKey);
                $table->index([$morphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->foreign('role_id')->references('id')->on($tables['roles'])->onDelete('cascade');
                $table->primary(['role_id', $morphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
            });
        }

        if (! Schema::hasTable($tables['role_has_permissions'])) {
            Schema::create($tables['role_has_permissions'], function (Blueprint $table) use ($tables) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on($tables['permissions'])->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on($tables['roles'])->onDelete('cascade');
                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
        }

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tables = config('permission.table_names');

        Schema::dropIfExists($tables['role_has_permissions']);
        Schema::dropIfExists($tables['model_has_roles']);
        Schema::dropIfExists($tables['model_has_permissions']);
        Schema::dropIfExists($tables['roles']);
        Schema::dropIfExists($tables['permissions']);
    }
};
