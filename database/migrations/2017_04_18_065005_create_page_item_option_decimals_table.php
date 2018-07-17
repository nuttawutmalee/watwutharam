<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageItemOptionDecimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('page_item_option_decimals');

        Schema::create('page_item_option_decimals', function (Blueprint $table) {
            $table->uuid('page_item_option_id');
            $table->primary('page_item_option_id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->decimal('option_value', 10, 3)->nullable();
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
        Schema::dropIfExists('page_item_option_decimals');
    }
}
