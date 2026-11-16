<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [category slug, name, author, guest_price, member_price, stock]
        $books = [
            ['literature', 'Dế Mèn Phiêu Lưu Ký', 'Tô Hoài', 62000, 55000, 40],
            ['literature', 'Mắt Biếc', 'Nguyễn Nhật Ánh', 98000, 88000, 35],
            ['literature', 'Cho Tôi Xin Một Vé Đi Tuổi Thơ', 'Nguyễn Nhật Ánh', 86000, 77000, 30],
            ['literature', 'The Little Prince', 'Antoine de Saint-Exupéry', 110000, 99000, 25],
            ['self-development', 'Atomic Habits', 'James Clear', 189000, 169000, 50],
            ['self-development', 'Đắc Nhân Tâm', 'Dale Carnegie', 98000, 88000, 60],
            ['self-development', 'Deep Work', 'Cal Newport', 175000, 158000, 18],
            ['textbooks-it', 'Clean Code', 'Robert C. Martin', 320000, 288000, 15],
            ['textbooks-it', 'Introduction to Algorithms', 'Thomas H. Cormen', 650000, 585000, 8],
            ['textbooks-it', 'Laravel: Up & Running', 'Matt Stauffer', 410000, 369000, 12],
            ['foreign-languages', 'English Grammar in Use', 'Raymond Murphy', 215000, 193000, 45],
            ['foreign-languages', 'Minna no Nihongo – Sơ cấp 1', 'Nhiều tác giả', 145000, 130000, 0], // out of stock demo
        ];

        foreach ($books as [$slug, $name, $author, $guest, $member, $stock]) {
            Product::updateOrCreate(['name' => $name], [
                'category_id' => Category::where('slug', $slug)->value('id'),
                'author' => $author,
                'description' => "{$name} by {$author}.",
                'guest_price' => $guest,
                'member_price' => $member,
                'stock' => $stock,
                'status' => Product::STATUS_ACTIVE,
            ]);
        }
    }
}
