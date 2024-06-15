<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_receipts', function (Blueprint $table) {
            $table->increments('receipt_id');
            $table->integer('agency_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->date('receipt_date')->nullable();
            $table->decimal('total_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_receipts');
    }
}
