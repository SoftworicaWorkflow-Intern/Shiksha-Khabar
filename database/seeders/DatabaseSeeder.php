<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Education News', 'education-news'],
            ['School Education', 'school-education'],
            ['SEE Examination', 'see'],
            ['NEB / +2', 'neb-plus-two'],
            ['Universities', 'universities'],
            ['Exam & Results', 'exam-results'],
            ['Scholarships', 'scholarships'],
            ['Admissions', 'admissions'],
            ['Teacher Vacancies', 'teacher-vacancies'],
            ['Career Guidance', 'career-guidance'],
            ['Study Abroad', 'study-abroad'],
            ['Articles & Opinions', 'articles-opinions'],
        ] as $order => [$name, $slug]) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => $order]);
        }
    }
}
