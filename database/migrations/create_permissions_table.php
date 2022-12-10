<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissionTableName = config('acl.database.permission.table_name');
        $permissionForeignKey = config('acl.database.permission.foreign_key');

        Schema::create($permissionTableName, function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
        });

        Schema::create(config('acl.database.permission_roles.table_name'), function (Blueprint $table) use ($permissionTableName, $permissionForeignKey) {

            $roleTableName = config('acl.database.role.table_name');
            $roleForeignKey = config('acl.database.role.foreign_key');

            $table->unsignedBigInteger($permissionForeignKey);
            $table->foreign($permissionForeignKey)->references('id')->on($permissionTableName)->onUpdate('cascade');

            $table->unsignedInteger($roleForeignKey);
            $table->foreign($roleForeignKey)->references('id')->on($roleTableName)->onUpdate('cascade');

            $table->unique([$permissionForeignKey, $roleForeignKey]);
        });

        Schema::create(config('acl.database.permission_users.table_name'), function (Blueprint $table) use ($permissionTableName, $permissionForeignKey) {

            $permissionUsersUserTableNames = config('acl.database.permission_users.user_table_name');
            $permissionUsersUserForeignKey = config('acl.database.permission_users.user_foreign_key');

            $table->unsignedBigInteger($permissionForeignKey);
            $table->foreign($permissionForeignKey)->references('id')->on($permissionTableName)->onUpdate('cascade');

            $table->unsignedBigInteger($permissionUsersUserForeignKey);
            $table->foreign($permissionUsersUserForeignKey)->references('id')->on($permissionUsersUserTableNames)->onUpdate('cascade');
            $table->boolean('is_able');

            $table->unique([$permissionUsersUserForeignKey, $permissionForeignKey]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('acl.database.permission_users.table_name'));
        Schema::dropIfExists(config('acl.database.permission_roles.table_name'));
        Schema::dropIfExists(config('acl.database.permission.table_name'));
    }
}
