<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSongRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('song_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->default('[Song title not found]');
            $table->string('original_url');
            $table->string('download_url')->nullable();
            $table->string('by')->default('Unknown');
            $table->string('length')->default('N/A');
            $table->boolean('playing')->default(false);
            $table->boolean('played')->default(false);
            $table->integer('votes')->default(0);
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
        Schema::dropIfExists('song_requests');
    }
}
