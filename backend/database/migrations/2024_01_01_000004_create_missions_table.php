<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('providers')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->enum('category', [
                'it_support',
                'plumbing',
                'electrical',
                'network_installation',
                'hvac',
                'security',
                'maintenance',
                'construction',
                'other'
            ]);
            $table->string('location_city');
            $table->string('location_address')->nullable();
            $table->string('location_country')->nullable();
            $table->decimal('location_zipcode', 10, 0)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('intervention_date');
            $table->time('intervention_time')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->enum('status', [
                'draft',
                'open',
                'in_review',
                'assigned',
                'in_progress',
                'completed',
                'cancelled',
                'disputed'
            ])->default('draft');
            $table->json('attachments')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
