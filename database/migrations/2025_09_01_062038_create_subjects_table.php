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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('university_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('credits')->default(0);
            $table->enum('subject_type', ['core', 'elective', 'practical', 'project'])->default('core');
            $table->string('prerequisite_subjects')->nullable(); // JSON or comma-separated IDs
            $table->string('syllabus_file')->nullable(); // File path to syllabus
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index(['course_id', 'semester_id', 'status']);
            $table->index(['university_id', 'status']);
            $table->index('subject_type');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
