<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('head_name');
            $table->string('village');
            $table->json('demographics')->nullable();
            $table->json('lifestyle')->nullable();
            $table->json('vitals')->nullable();
            $table->json('medical_history')->nullable();
            $table->json('addictions')->nullable();
            $table->json('mental_health')->nullable();
            $table->json('vaccination')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('families');
    }
};
