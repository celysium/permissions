<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    const ARTISAN_CACHE_TABLE_CREATION = 'php artisan cache:table';

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
            Artisan::call(static::ARTISAN_CACHE_TABLE_CREATION);
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
