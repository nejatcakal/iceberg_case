<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_address');
            $table->date('appointment_date');
            $table->time('appointment_time');

            $table->unsignedBigInteger('contact_id')->nullable()->default(null);
            $table->foreign('contact_id')->references('id')->on('contacts');

            $table->unsignedBigInteger('who_will_meet')->nullable()->default(null);
            $table->foreign('who_will_meet')->references('id')->on('users');

            $table->time('leave_office');
            $table->time('return_to_office');

            $table->unsignedBigInteger('added_by');
            $table->foreign('added_by')->references('id')->on('users');
            $table->timestamp('added_at');

            $table->boolean('delete')->default(false);
            $table->datetime('deleted_at')->nullable()->default(null);
            $table->unsignedBigInteger('deleted_by')->nullable()->default(null);
            $table->foreign('deleted_by')->references('id')->on('users');

            $table->boolean('edited')->default(false);
            $table->datetime('edited_at')->nullable()->default(null);
            $table->unsignedBigInteger('edited_by')->nullable()->default(null);
            $table->foreign('edited_by')->references('id')->on('users');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
