<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('questionnaire_responses', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('product_id')->nullable();
            $table->string('questionnaire_instance_id')->nullable();
            $table->json('responses'); // Store all answers as JSON
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questionnaire_responses');
    }
};
