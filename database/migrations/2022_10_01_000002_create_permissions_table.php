<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
        });

        Schema::create('permission_roles', function (Blueprint $table) {

            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade');

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade');

            $table->unique(['permission_id', 'role_id']);
        });

        Schema::create('permission_users', function (Blueprint $table) {

            $userTable = config('permission.user.table');
            $userForeignKey = config('permission.user.foreign_key');
            $userRelationId = config('permission.user.relation_id');
            $userType = config('permission.user.type');

            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade');

            $table->$userType($userForeignKey);
            $table->foreign($userForeignKey)->references($userRelationId)->on($userTable)->onUpdate('cascade');
            $table->boolean('is_able')->default(false);

            $table->unique([$userForeignKey, 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_users');
        Schema::dropIfExists('permission_roles');
        Schema::dropIfExists('permissions');
    }
};
