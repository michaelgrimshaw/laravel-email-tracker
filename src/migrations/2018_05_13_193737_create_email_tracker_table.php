<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateEmailTrackerTable
 */
class CreateEmailTrackerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('mailtracker.table_names.email_tracker'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tracker_id')->unsigned();
            $table->nullableMorphs('recipient');
            $table->nullableMorphs('linked_to');
            $table->enum('distribution_type', ['to', 'cc', 'bcc'])->default('to');
            $table->string('email');
            $table->string('category')->nullable();
            $table->string('queue')->nullable();
            $table->string('mail_class');
            $table->timestamps();

            $table->index(['tracker_id', 'recipient_id', 'recipient_type', 'linked_to_id', 'linked_to_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('mailtracker.table_names.email_tracker'));
    }
}
