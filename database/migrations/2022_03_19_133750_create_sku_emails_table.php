<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkuEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sku_emails', function (Blueprint $table) {
            $table->id();
            $table->integer('product_sku');
            $table->unsignedBigInteger('email_id');
            $table->timestamps();

            $table->unique(['product_sku', 'email_id'], 'product_email_uk');
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sku_emails');
    }
}
