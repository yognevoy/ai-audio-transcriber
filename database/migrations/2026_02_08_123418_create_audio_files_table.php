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
        Schema::create('audio_files', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('filename');
            $table->string('path');
            $table->bigInteger('size');
            $table->string('mime_type');
            $table->decimal('duration', 10, 2)->nullable();

            $table->enum('status', ['uploaded', 'processing', 'completed', 'failed'])
                ->default('uploaded');
            $table->text('error_message')->nullable();

            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};
