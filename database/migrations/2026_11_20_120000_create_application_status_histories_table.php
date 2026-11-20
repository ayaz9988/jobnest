<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestamps();
        });

        $now = now();

        DB::table('applications')
            ->select(['id', 'status', 'applied_at', 'created_at'])
            ->orderBy('id')
            ->chunkById(500, function ($applications) use ($now): void {
                $rows = [];

                foreach ($applications as $application) {
                    $rows[] = [
                        'application_id' => $application->id,
                        'from_status' => null,
                        'to_status' => $application->status,
                        'created_at' => $application->applied_at ?? $application->created_at ?? $now,
                        'updated_at' => $application->applied_at ?? $application->created_at ?? $now,
                    ];
                }

                DB::table('application_status_histories')->insert($rows);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
    }
};
