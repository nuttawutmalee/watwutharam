<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCmsRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::dropIfExists('cms_roles');
		
        Schema::create('cms_roles', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_developer')->default(false);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('allow_structure')->default(false);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('allow_content')->default(false);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('allow_user')->default(false);
            $table->uuid('updated_by');
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
        //
        Schema::dropIfExists('cms_roles');
    }
}
