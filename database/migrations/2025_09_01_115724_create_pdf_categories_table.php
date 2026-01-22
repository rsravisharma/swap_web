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
        Schema::create('pdf_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedInteger('level')->default(1)->comment('1=Category, 2=Subcategory, 3=Child');
            $table->foreign('parent_id')->references('id')->on('pdf_categories')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['slug', 'parent_id']);
            $table->index(['parent_id', 'level']);
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_categories');
    }
};
