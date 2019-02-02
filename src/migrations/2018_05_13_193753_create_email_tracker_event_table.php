<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateEmailTrackerEventTable
 */
class CreateEmailTrackerEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('mailtracker.table_names.email_tracker_event'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer(config('mailtracker.table_names.email_tracker') . '_id')->unsigned();
            $table->string('status');
            $table->text('event_data');
            $table->timestamps();

            $table->foreign(config('mailtracker.table_names.email_tracker') . '_id')
                ->references('id')
                ->on(config('mailtracker.table_names.email_tracker'))
                ->onDelete('cascade');

            $table->index([config('mailtracker.table_names.email_tracker') . '_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('mailtracker.table_names.email_tracker_event'));
    }
}
