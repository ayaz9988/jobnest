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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_listing_id')->constrained('job_listings')->onDelete('cascade');
            $table->foreignId('resume_id')->nullable()->constrained('resumes')->onDelete('set null');
            $table->enum('status', [
                'applied', 
                'reviewed', 
                'interview', 
                'withdraw', 
                'hired', 
                'rejected'
            ])->default('applied');
            $table->text('cover_letter')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->unique(['job_listing_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
