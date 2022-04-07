<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGalleryImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('image_id')->unsigned();
            $table->foreign('image_id')
            ->references('id')
            ->on('images')
            ->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users') 
                ->onDelete('cascade');
            $table->timestamps();
        });


       /*
        DB::statement('alter table gallery_images drop constraint gallery_images_user_id_foreign,
                   add constraint gallery_images_user_id_foreign
                   foreign key (user_id)
                   references users(id)
                   on delete cascade;'
       Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropForeign(['gallery_images_image_id_foreign']);
            $table->foreign('user_id')
            ->references('id')
            ->on('images') 
            ->onDelete('cascade')
            ->change();
            $table->dropForeign(['gallery_images_user_id_foreign']);
            $table->foreign('user_id')
            ->references('id')
            ->on('users') 
            ->onDelete('cascade')
            ->change();
        });*/
       

       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gallery_images');
    }
}
