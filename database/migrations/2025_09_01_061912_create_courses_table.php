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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('duration')->nullable(); // e.g., "4 years", "2 years"
            $table->enum('degree_type', ['undergraduate', 'postgraduate', 'diploma', 'certificate'])->default('undergraduate');
            $table->foreignId('university_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('total_semesters')->default(0);
            $table->decimal('fees_per_semester', 10, 2)->nullable();
            $table->string('eligibility_criteria')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index(['university_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index('degree_type');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
