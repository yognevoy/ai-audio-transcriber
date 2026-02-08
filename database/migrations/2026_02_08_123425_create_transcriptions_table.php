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
        Schema::create('transcriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('audio_file_id');
            $table->foreign('audio_file_id')
                ->references('id')
                ->on('audio_files')
                ->onDelete('cascade');

            $table->longText('content');
            $table->longText('raw_content');

            $table->enum('status', ['processing', 'completed', 'failed'])
                ->default('processing');
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcriptions');
    }
};
