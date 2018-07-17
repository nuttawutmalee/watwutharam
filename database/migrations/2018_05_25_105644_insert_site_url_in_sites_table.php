<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertSiteUrlInSitesTable extends Migration
{
/**
     * Run the migrations.
     *
     * @return void
 */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('site_url')->nullable()->after('domain_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('site_url');
        });
    }
}
