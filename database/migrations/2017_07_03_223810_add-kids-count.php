<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Knowfox\Models\Concept;

class AddKidsCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('concepts', function (Blueprint $table) {
            $table->unsignedInteger('children_count')->default(0);
        });

        Concept::chunk(200, function ($concepts) {

            foreach ($concepts as $concept) {
                echo "- {$concept->id}\n";
                $concept->children_count = Concept::where('parent_id', $concept->id)->count();
                $concept->save();
            }
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
