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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
        });

        Schema::create('role_users', function (Blueprint $table) {

            $userTable = config('acl.user.table');
            $userForeignKey = config('acl.user.foreign_key');

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade');

            $table->unsignedBigInteger($userForeignKey);
            $table->foreign($userForeignKey)->references('id')->on($userTable)->onUpdate('cascade');

            $table->unique([$userForeignKey, 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_users');
        Schema::dropIfExists('roles');
    }
};
