<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageAndStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('concepts', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->string('status', 20)->default('private');
            $table->boolean('is_flagged')->default(0);
            $table->string('slug')->nullable()->unique();

            $table->boolean('is_task')->default(0);
            $table->boolean('is_done')->default(0);
            $table->timestamp('due_at')->nullable();
            $table->timestamp('remind_at')->nullable();

            $table->smallInteger('weight')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('concepts', function (Blueprint $table) {
            //
        });
    }
}
