<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('site_languages');

        Schema::create('site_languages', function (Blueprint $table) {
            $table->uuid('site_id');
            $table->uuid('language_code');
            $table->integer('display_order');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_active')->default(true);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_main')->default(false);
            $table->timestamps();
            $table->primary(['site_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_languages');
    }
}
