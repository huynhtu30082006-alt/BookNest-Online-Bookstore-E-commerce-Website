<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Literature', 'literature', 'Novels, short stories and classic literature.'],
            ['Self-development', 'self-development', 'Books on habits, mindset and career growth.'],
            ['Textbooks & IT', 'textbooks-it', 'Textbooks and programming / IT references.'],
            ['Foreign languages', 'foreign-languages', 'English, Japanese and Korean learning materials.'],
        ];

        foreach ($categories as [$name, $slug, $description]) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'is_active' => true]);
        }
    }
}
