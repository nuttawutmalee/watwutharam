<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalItemOptionDecimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('global_item_option_decimals');

        Schema::create('global_item_option_decimals', function (Blueprint $table) {
            $table->uuid('global_item_option_id');
            $table->primary('global_item_option_id');
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
        Schema::dropIfExists('global_item_option_decimals');
    }
}
