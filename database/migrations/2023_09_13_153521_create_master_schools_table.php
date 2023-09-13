<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_schools', function (Blueprint $table) {
            $table->id();
            $table->string('school_name', 255);
            $table->string('school_dir', 255);
            $table->string('status', 255);
            $table->string('school_url', 255);
            $table->string('school_uid', 255);
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
        Schema::dropIfExists('master_schools');
    }
};
