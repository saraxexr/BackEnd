<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->bigIncrements("requestId");
            $table->text("description");
            $table->string("requestStatus")->default("0");  // It is a way to ensure the reqest status whether processed or not example 0: Pending, 1:in progress, 2:completed, 3: canceled.
            $table->string("requestNum")->unique();
            $table->json("address");
            $table->mediumText("model");
            $table->string("field");
            $table->unsignedBigInteger("brandId");
            $table->string("quantity");
            $table->longText("amounts")->nullable();
            $table->string("finalAmount")->default("0");
            $table->unsignedBigInteger("clientId");
            $table->unsignedBigInteger("supplierId")->nullable();
            $table->string("shipperName")->nullable();
            $table->foreign("clientId")->references("clientId")->on("clients")->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->foreign("supplierId")->references("supplierId")->on("suppliers")->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->foreign("brandId")->references("brandId")->on("brands")->onDelete("CASCADE")->onUpdate("CASCADE");
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
        Schema::dropIfExists('requests');
    }
}
