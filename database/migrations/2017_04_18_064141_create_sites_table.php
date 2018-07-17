<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('sites');

        Schema::create('sites', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('domain_name')->unique();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('description')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('sites');
    }
}