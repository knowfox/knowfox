<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->integer('concept_id')->unsigned();
            $table->foreign('concept_id')->references('id')->on('concepts');
            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->boolean('is_done')->default(false)->index();
            $table->date('due_at')->nullable()->index();
            $table->date('done_at')->nullable()->index();
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
        Schema::dropIfExists('items');
    }
}
