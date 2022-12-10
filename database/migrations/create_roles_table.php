<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $roleTableName = config('acl.database.role.table_name');

        Schema::create($roleTableName, function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
        });

        Schema::create(config('acl.database.role_users.table_name'), function (Blueprint $table) use ($roleTableName) {

            $roleForeignKey = config('acl.database.role.foreign_key');
            $roleUsersUserTableNames = config('acl.database.role_users.user_table_name');
            $roleUsersUserForeignKey = config('acl.database.role_users.user_foreign_key');

            $table->unsignedBigInteger($roleForeignKey);
            $table->foreign($roleForeignKey)->references('id')->on($roleTableName)->onUpdate('cascade');

            $table->unsignedBigInteger($roleUsersUserForeignKey);
            $table->foreign($roleUsersUserForeignKey)->references('id')->on($roleUsersUserTableNames)->onUpdate('cascade');

            $table->unique([$roleUsersUserForeignKey, $roleForeignKey]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('acl.database.role.table_name'));
    }
};
