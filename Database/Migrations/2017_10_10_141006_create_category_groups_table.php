<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('netcore_category__category_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique()->index();
            $table->string('title');
            $table->boolean('has_icons')->default(false);
            $table->boolean('icons_for_only_roots')->default(false);
            $table->enum('icons_type', ['select2', 'file'])->default('select2');
            $table->string('icons_presenter_class')->nullable();
            $table->unsignedInteger('levels')->nullable();
            $table->text('file_icons')->nullable();
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
        Schema::dropIfExists('netcore_category__category_groups');
    }
}
