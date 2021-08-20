<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger("supplierId");
            $table->string("companyInEnglish");
            $table->string("companyInArabic");
            $table->string("companyCertificate")->nullable();
            $table->string("verified")->default("0"); // It is a way to ensure the supplier's account whether verified or not example 0: Not verified, 1:Verified, 2:Suspended
            $table->foreign("supplierId")->references("uid")->on("users_info")->onDelete("CASCADE")->onUpdate("CASCADE");
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
        Schema::dropIfExists('suppliers');
    }
}
