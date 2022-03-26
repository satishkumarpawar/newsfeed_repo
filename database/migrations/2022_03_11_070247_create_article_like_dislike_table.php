<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleLikeDislikeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_like_dislike', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id')->unsigned()->default(0);
            $table->foreign('article_id')
                ->references('id')
                ->on('articles');
            $table->integer('likes')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_like_dislike');
    }
}
