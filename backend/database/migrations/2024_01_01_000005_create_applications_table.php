<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->decimal('proposed_price', 10, 2)->nullable();
            $table->date('proposed_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'withdrawn'
            ])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['mission_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
