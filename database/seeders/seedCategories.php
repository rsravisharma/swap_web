<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PdfBook;
use App\Models\PdfCategory;

class seedCategories extends Seeder
{
    public function run(): void
    {
        // ===== MAIN CATEGORIES (Level 1) =====
        $academic = PdfCategory::firstOrCreate(
            ['slug' => 'academic'],
            ['name' => 'Academic Books', 'level' => 1]
        );

        $fiction = PdfCategory::firstOrCreate(
            ['slug' => 'fiction'],
            ['name' => 'Fiction', 'level' => 1]
        );

        $selfHelp = PdfCategory::firstOrCreate(
            ['slug' => 'self-help'],
            ['name' => 'Self Help', 'level' => 1]
        );

        // ✅ NEW: Himachal Pradesh Competitive Exams (Level 1)
        $hpCompetitive = PdfCategory::firstOrCreate(
            ['slug' => 'hp-competitive-exams'],
            ['name' => 'Himachal Pradesh Competitive Exam', 'level' => 1]
        );

        // ===== ACADEMIC SUBCATEGORIES (Level 2) =====
        PdfCategory::firstOrCreate(
            ['slug' => 'engineering', 'parent_id' => $academic->id],
            ['name' => 'Engineering', 'level' => 2, 'parent_id' => $academic->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'medical', 'parent_id' => $academic->id],
            ['name' => 'Medical', 'level' => 2, 'parent_id' => $academic->id]
        );

        // ===== FICTION SUBCATEGORIES (Level 2-3) =====
        $novels = PdfCategory::firstOrCreate(
            ['slug' => 'novels', 'parent_id' => $fiction->id],
            ['name' => 'Novels', 'level' => 2, 'parent_id' => $fiction->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'romance', 'parent_id' => $novels->id],
            ['name' => 'Romance', 'level' => 3, 'parent_id' => $novels->id]
        );

        // ===== HP COMPETITIVE EXAMS HIERARCHY (Level 2-3) =====
        // ✅ Patwari Exam (Level 2 - Subcategory)
        $patwariExam = PdfCategory::firstOrCreate(
            ['slug' => 'patwari-exam', 'parent_id' => $hpCompetitive->id],
            ['name' => 'Patwari Exam', 'level' => 2, 'parent_id' => $hpCompetitive->id]
        );

        // ✅ SUBJECTS (Level 3 - Child Categories) - English
        PdfCategory::firstOrCreate(
            ['slug' => 'himachal-gk-en', 'parent_id' => $patwariExam->id],
            ['name' => 'Himachal GK (English)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'computer-education-en', 'parent_id' => $patwariExam->id],
            ['name' => 'Computer Education (English)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'language-en', 'parent_id' => $patwariExam->id],
            ['name' => 'Language (English)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'maths-en', 'parent_id' => $patwariExam->id],
            ['name' => 'Maths (English)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'reasoning-en', 'parent_id' => $patwariExam->id],
            ['name' => 'Reasoning (English)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'science-en', 'parent_id' => $patwariExam->id],
            ['name' => 'Science (English)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        // ✅ SUBJECTS (Level 3 - Child Categories) - Hindi
        PdfCategory::firstOrCreate(
            ['slug' => 'himachal-gk-hi', 'parent_id' => $patwariExam->id],
            ['name' => 'हिमाचल जीके (Hindi)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'computer-education-hi', 'parent_id' => $patwariExam->id],
            ['name' => 'कंप्यूटर शिक्षा (Hindi)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'language-hi', 'parent_id' => $patwariExam->id],
            ['name' => 'भाषा (Hindi)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'maths-hi', 'parent_id' => $patwariExam->id],
            ['name' => 'गणित (Hindi)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'reasoning-hi', 'parent_id' => $patwariExam->id],
            ['name' => 'रीजनिंग (Hindi)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        PdfCategory::firstOrCreate(
            ['slug' => 'science-hi', 'parent_id' => $patwariExam->id],
            ['name' => 'विज्ञान (Hindi)', 'level' => 3, 'parent_id' => $patwariExam->id]
        );

        $this->command->info('✅ HP Patwari Exam categories seeded successfully!');
    }
}
