<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->id()->index(); // Primary key
            $table->unsignedBigInteger('campaign_id')->index();
            $table->unsignedBigInteger('term_id')->index();
            $table->date('event_date'); // Date of the event
            $table->integer('event_hour'); // Hour of the event
            $table->decimal('revenue', 10, 4); // Revenue for the event
            $table->timestamp('monetization_timestamp');
            $table->timestamps(); // Created at and Updated at timestamps

            $table->foreign('campaign_id', 'fk_stats_campaignz')
                  ->references('id')
                  ->on('campaigns')
                  ->onDelete('cascade');

                  $table->foreign('term_id', 'fk_stats_terms')
                  ->references('id')
                  ->on('terms')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};
