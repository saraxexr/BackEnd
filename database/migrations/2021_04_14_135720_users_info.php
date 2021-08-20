<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersInfo extends Migration
{
    public function up()
    {
        Schema::create('users_info', function (Blueprint $table) {
            $table->bigIncrements("uid");
            $table->string("name");
            $table->string("nameInArabic");
            $table->string("password");
            $table->string("email")->unique();
            $table->string("phone");
            $table->longText("token")->nullable();
            $table->string("rememberToken")->nullable();
            $table->string("userType")->default("3"); // 0-ADMIN, 1-MODERATORS, 2-SUPPLIERS, 3-CLIENT;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_info');
    }
}
