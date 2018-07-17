<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryNameItemMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('category_name_item_mappings');

        Schema::create('category_name_item_mappings', function (Blueprint $table) {
            $table->uuid('item_id');
            $table->uuid('category_name_id');
            $table->primary(['item_id', 'category_name_id']);
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
        Schema::dropIfExists('category_name_item_mappings');
    }
}
