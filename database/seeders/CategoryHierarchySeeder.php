<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildSubCategory;
use Illuminate\Support\Facades\DB;

class CategoryHierarchySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks outside of transaction
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            DB::transaction(function () {
                // Clear existing data in correct order
                if (DB::getSchemaBuilder()->hasTable('items')) {
                    DB::table('items')->whereNotNull('child_sub_category_id')->update(['child_sub_category_id' => null]);
                    DB::table('items')->whereNotNull('sub_category_id')->update(['sub_category_id' => null]);
                    DB::table('items')->whereNotNull('category_id')->update(['category_id' => null]);
                }
                
                // Now truncate the tables
                ChildSubCategory::truncate();
                SubCategory::truncate();
                Category::truncate();

                $categoriesData = $this->getCategoriesData();

                foreach ($categoriesData['categories'] as $categoryData) {
                    // Create category
                    $category = Category::create([
                        'name' => $categoryData['name'],
                        'description' => $this->getCategoryDescription($categoryData['name']),
                        'icon' => $this->getCategoryIcon($categoryData['name']),
                        'sort_order' => array_search($categoryData['name'], array_column($categoriesData['categories'], 'name')) + 1,
                    ]);

                    // Create subcategories
                    foreach ($categoryData['sub_categories'] as $index => $subCategoryData) {
                        $subCategory = SubCategory::create([
                            'category_id' => $category->id,
                            'name' => $subCategoryData['name'],
                            'description' => $this->getSubCategoryDescription($subCategoryData['name']),
                            'icon' => $this->getSubCategoryIcon($subCategoryData['name']),
                            'sort_order' => $index + 1,
                        ]);

                        // Create child subcategories if they exist
                        if (isset($subCategoryData['child_sub_categories'])) {
                            foreach ($subCategoryData['child_sub_categories'] as $childIndex => $childSubCategoryData) {
                                ChildSubCategory::create([
                                    'sub_category_id' => $subCategory->id,
                                    'name' => $childSubCategoryData['name'],
                                    'description' => $this->getChildSubCategoryDescription($childSubCategoryData['name']),
                                    'sort_order' => $childIndex + 1,
                                ]);
                            }
                        }
                    }
                }
            });
            
            $this->command->info('Category hierarchy seeded successfully!');
            
        } finally {
            // Re-enable foreign key checks in finally block to ensure it always runs
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function getCategoriesData(): array
    {
        return [
            "categories" => [
                [
                    "name" => "Books",
                    "sub_categories" => [
                        ["name" => "Textbooks"],
                        ["name" => "Reference Books"],
                        ["name" => "Fiction Books"],
                        ["name" => "Non-Fiction Books"],
                        ["name" => "School Books"],
                        ["name" => "College Books"],
                        ["name" => "University Books"],
                        ["name" => "Magazines & Journals"]
                    ]
                ],
                [
                    "name" => "Notes & Study Material",
                    "sub_categories" => [
                        ["name" => "Lecture Notes"],
                        ["name" => "Study Guides"],
                        ["name" => "Assignment Solutions"],
                        ["name" => "Question Banks"],
                        ["name" => "Previous Year Papers"],
                        [
                            "name" => "Coaching Institute Notes",
                            "child_sub_categories" => [
                                ["name" => "Vision IAS"],
                                ["name" => "Vajiram & Ravi"],
                                ["name" => "Allen"],
                                ["name" => "Resonance"],
                                ["name" => "Made Easy"],
                                ["name" => "Other Institutes"]
                            ]
                        ]
                    ]
                ],
                [
                    "name" => "Competitive Exams",
                    "sub_categories" => [
                        [
                            "name" => "UPSC",
                            "child_sub_categories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"],
                                ["name" => "Question Banks"],
                                ["name" => "Mock Papers"]
                            ]
                        ],
                        [
                            "name" => "SSC",
                            "child_sub_categories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "Banking (IBPS, SBI, RBI)",
                            "child_sub_categories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "Railways (RRB)",
                            "child_sub_categories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "Defence (NDA, CDS, AFCAT)",
                            "child_sub_categories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "State PSCs",
                            "child_sub_categories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "JEE / NEET",
                            "child_sub_categories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"],
                                ["name" => "Question Banks"],
                                ["name" => "Mock Papers"]
                            ]
                        ]
                    ]
                ],
                [
                    "name" => "Electronics",
                    "sub_categories" => [
                        ["name" => "Laptops"],
                        ["name" => "Computers"],
                        ["name" => "Laptop & Computer Accessories"],
                        ["name" => "Phones"],
                        ["name" => "Tablets"],
                        ["name" => "Phone Accessories"],
                        ["name" => "Cameras"],
                        ["name" => "Camera Accessories"],
                        ["name" => "Headphones & Earphones"],
                        ["name" => "Smartwatches & Wearables"],
                        ["name" => "Project Kits & Robotics"]
                    ]
                ],
                [
                    "name" => "Stationery & Supplies",
                    "sub_categories" => [
                        ["name" => "Writing Materials"],
                        ["name" => "Notebooks & Registers"],
                        ["name" => "Art Supplies"],
                        ["name" => "Drawing Tools"],
                        ["name" => "Office Supplies"]
                    ]
                ],
                [
                    "name" => "Lab Equipment",
                    "sub_categories" => [
                        ["name" => "Laboratory Instruments"],
                        ["name" => "Safety Equipment"],
                        ["name" => "Measuring Tools"],
                        ["name" => "Lab Consumables"]
                    ]
                ],
                [
                    "name" => "Clothing & Accessories",
                    "sub_categories" => [
                        ["name" => "Clothes (Uniforms, Casuals)"],
                        ["name" => "Shoes"],
                        ["name" => "Bags (Backpacks, Laptop Bags)"],
                        ["name" => "Belts, Ties & Accessories"]
                    ]
                ],
                [
                    "name" => "Furniture & Hostel Items",
                    "sub_categories" => [
                        ["name" => "Study Tables"],
                        ["name" => "Chairs"],
                        ["name" => "Beds & Mattresses"],
                        ["name" => "Storage (Cupboards, Shelves)"],
                        ["name" => "Lamps & Lighting"],
                        ["name" => "Daily Essentials (Fans, Cooktops, Power Banks, Extension Boards)"]
                    ]
                ],
                [
                    "name" => "Sports & Fitness",
                    "sub_categories" => [
                        ["name" => "Sports Equipment"],
                        ["name" => "Gym Equipment"],
                        ["name" => "Bicycles"],
                        ["name" => "Yoga & Fitness Gear"]
                    ]
                ],
                [
                    "name" => "Musical Instruments & Hobbies",
                    "sub_categories" => [
                        ["name" => "Musical Instruments"],
                        ["name" => "Art & Craft Materials"],
                        ["name" => "Board Games & Puzzles"],
                        ["name" => "Event & Fest Items"]
                    ]
                ],
                [
                    "name" => "Miscellaneous",
                    "sub_categories" => [
                        ["name" => "Calculators"],
                        ["name" => "Project Materials"],
                        ["name" => "Everyday Essentials"],
                        ["name" => "Others"]
                    ]
                ]
            ]
        ];
    }

    private function getCategoryDescription(string $name): string
    {
        $descriptions = [
            'Books' => 'All types of books including textbooks, reference books, fiction and non-fiction',
            'Notes & Study Material' => 'Study notes, guides, and educational materials',
            'Competitive Exams' => 'Materials for competitive examinations',
            'Electronics' => 'Electronic devices and accessories',
            'Stationery & Supplies' => 'Writing materials and office supplies',
            'Lab Equipment' => 'Laboratory instruments and equipment',
            'Clothing & Accessories' => 'Clothing items and fashion accessories',
            'Furniture & Hostel Items' => 'Furniture and hostel essentials',
            'Sports & Fitness' => 'Sports equipment and fitness gear',
            'Musical Instruments & Hobbies' => 'Musical instruments and hobby materials',
            'Miscellaneous' => 'Other items and everyday essentials',
        ];

        return $descriptions[$name] ?? "Items related to {$name}";
    }

    private function getCategoryIcon(string $name): string
    {
        $icons = [
            'Books' => 'book',
            'Notes & Study Material' => 'note',
            'Competitive Exams' => 'quiz',
            'Electronics' => 'laptop',
            'Stationery & Supplies' => 'edit',
            'Lab Equipment' => 'science',
            'Clothing & Accessories' => 'checkroom',
            'Furniture & Hostel Items' => 'chair',
            'Sports & Fitness' => 'sports',
            'Musical Instruments & Hobbies' => 'music_note',
            'Miscellaneous' => 'category',
        ];

        return $icons[$name] ?? 'category';
    }

    private function getSubCategoryDescription(string $name): string
    {
        return "Items related to {$name}";
    }

    private function getSubCategoryIcon(string $name): string
    {
        return 'category';
    }

    private function getChildSubCategoryDescription(string $name): string
    {
        return "Items related to {$name}";
    }
}
