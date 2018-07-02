<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetcoreCategoryCategoryIconsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('netcore_category__category_icons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');

            $table->string('key');

            // Stapler fields.
            $table->string('icon_file_name');
            $table->string('icon_file_size');
            $table->string('icon_content_type');
            $table->dateTime('icon_updated_at');

            $table->unique(['category_id', 'key']);
            $table->foreign('category_id')->references('id')->on('netcore_category__categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('netcore_category__category_icons');
    }
}
