<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMainOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('main_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('no_invoice');
            $table->integer('no_meja')->nullable();
            $table->string('cashier')->nullable();
            $table->decimal('grandtotal', 10,2);
            $table->enum('payment', ['cash','transfer'])->nullable();
            $table->decimal('cash', 10,2)->nullable();
            $table->decimal('changes', 10,2)->nullable();
            $table->string('transfer_image')->nullable();
            $table->enum('status', ['debt', 'pending', 'checkout']);
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
        Schema::dropIfExists('main_orders');
    }
}
