<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('handle')->unique();
            $table->string('disk');
            $table->boolean('default')->default(false);
            $table->string('bucket')->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->ipAddress('created_ip')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->ipAddress('updated_ip')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('asset_sources');
    }
}
