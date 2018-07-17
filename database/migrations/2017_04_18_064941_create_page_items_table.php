<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('page_items');

        Schema::create('page_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('variable_name')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('description')->nullable();
            $table->integer('display_order');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_required')->default(false);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_active')->default(true);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_visible')->default(true);
            $table->uuid('page_id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->uuid('component_id')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->uuid('global_item_id')->nullable();
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
        Schema::dropIfExists('page_items');
    }
}
