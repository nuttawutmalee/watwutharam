<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('global_items');

        Schema::create('global_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('variable_name')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('description')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->integer('display_order')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_active')->default(true);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_visible')->default(true);
            $table->uuid('site_id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->uuid('component_id')->nullable();
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
        Schema::dropIfExists('global_items');
    }
}
