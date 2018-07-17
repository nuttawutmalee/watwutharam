<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedirectUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('redirect_urls');

        Schema::create('redirect_urls', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->integer('status_code')->default(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND);
            $table->text('source_url');
            $table->text('destination_url');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_active')->default(true);
            $table->uuid('site_id');
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
        Schema::dropIfExists('redirect_urls');
    }
}
