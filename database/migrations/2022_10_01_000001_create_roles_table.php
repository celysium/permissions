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
        });

        $config = config('permission.model');
        Schema::create('role_' . $config['name'], function (Blueprint $table) use ($config) {

            $userType = $config['type'];
            $userForeignKey = $config['foreign_key'];
            $userRelationId = $config['relation_id'];
            $userTable = $config['table'];

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade');

            $table->$userType($userForeignKey);
            $table->foreign($userForeignKey)->references($userRelationId)->on($userTable)->onUpdate('cascade');

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
        Schema::dropIfExists('role_' . config('permission.model.name'));
        Schema::dropIfExists('roles');
    }
};
