<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    const COMMAND_CACHE_TABLE = 'cache:table';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (
            !Schema::hasTable('cache')
            && config('acl.cache.storage') === 'database'
        ) {
            Artisan::call(static::COMMAND_CACHE_TABLE);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config('acl.cache.storage') === 'database') {
            Schema::dropIfExists('cache');
            Schema::dropIfExists('cache_locks');
        }
    }
};
