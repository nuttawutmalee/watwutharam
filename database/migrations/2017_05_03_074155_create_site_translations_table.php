<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('site_translations');

        Schema::create('site_translations', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->string('item_type');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('translated_text')->nullable();
            $table->uuid('language_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_translations');
    }
}
