<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ChildSubcategory;
use Illuminate\Support\Facades\DB;

class CategoryHierarchySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Clear existing data
            ChildSubcategory::truncate();
            Subcategory::truncate();
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
                foreach ($categoryData['subcategories'] as $index => $subcategoryData) {
                    $subcategory = Subcategory::create([
                        'category_id' => $category->id,
                        'name' => $subcategoryData['name'],
                        'description' => $this->getSubcategoryDescription($subcategoryData['name']),
                        'icon' => $this->getSubcategoryIcon($subcategoryData['name']),
                        'sort_order' => $index + 1,
                    ]);

                    // Create child subcategories if they exist
                    if (isset($subcategoryData['child_subcategories'])) {
                        foreach ($subcategoryData['child_subcategories'] as $childIndex => $childSubcategoryData) {
                            ChildSubcategory::create([
                                'subcategory_id' => $subcategory->id,
                                'name' => $childSubcategoryData['name'],
                                'description' => $this->getChildSubcategoryDescription($childSubcategoryData['name']),
                                'sort_order' => $childIndex + 1,
                            ]);
                        }
                    }
                }
            }
        });

        $this->command->info('Category hierarchy seeded successfully!');
    }

    private function getCategoriesData(): array
    {
        return [
            "categories" => [
                [
                    "name" => "Books",
                    "subcategories" => [
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
                    "subcategories" => [
                        ["name" => "Lecture Notes"],
                        ["name" => "Study Guides"],
                        ["name" => "Assignment Solutions"],
                        ["name" => "Question Banks"],
                        ["name" => "Previous Year Papers"],
                        [
                            "name" => "Coaching Institute Notes",
                            "child_subcategories" => [
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
                    "subcategories" => [
                        [
                            "name" => "UPSC",
                            "child_subcategories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"],
                                ["name" => "Question Banks"],
                                ["name" => "Mock Papers"]
                            ]
                        ],
                        [
                            "name" => "SSC",
                            "child_subcategories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "Banking (IBPS, SBI, RBI)",
                            "child_subcategories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "Railways (RRB)",
                            "child_subcategories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "Defence (NDA, CDS, AFCAT)",
                            "child_subcategories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "State PSCs",
                            "child_subcategories" => [
                                ["name" => "Books"],
                                ["name" => "Notes"]
                            ]
                        ],
                        [
                            "name" => "JEE / NEET",
                            "child_subcategories" => [
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
                    "subcategories" => [
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
                    "subcategories" => [
                        ["name" => "Writing Materials"],
                        ["name" => "Notebooks & Registers"],
                        ["name" => "Art Supplies"],
                        ["name" => "Drawing Tools"],
                        ["name" => "Office Supplies"]
                    ]
                ],
                [
                    "name" => "Lab Equipment",
                    "subcategories" => [
                        ["name" => "Laboratory Instruments"],
                        ["name" => "Safety Equipment"],
                        ["name" => "Measuring Tools"],
                        ["name" => "Lab Consumables"]
                    ]
                ],
                [
                    "name" => "Clothing & Accessories",
                    "subcategories" => [
                        ["name" => "Clothes (Uniforms, Casuals)"],
                        ["name" => "Shoes"],
                        ["name" => "Bags (Backpacks, Laptop Bags)"],
                        ["name" => "Belts, Ties & Accessories"]
                    ]
                ],
                [
                    "name" => "Furniture & Hostel Items",
                    "subcategories" => [
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
                    "subcategories" => [
                        ["name" => "Sports Equipment"],
                        ["name" => "Gym Equipment"],
                        ["name" => "Bicycles"],
                        ["name" => "Yoga & Fitness Gear"]
                    ]
                ],
                [
                    "name" => "Musical Instruments & Hobbies",
                    "subcategories" => [
                        ["name" => "Musical Instruments"],
                        ["name" => "Art & Craft Materials"],
                        ["name" => "Board Games & Puzzles"],
                        ["name" => "Event & Fest Items"]
                    ]
                ],
                [
                    "name" => "Miscellaneous",
                    "subcategories" => [
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

    private function getSubcategoryDescription(string $name): string
    {
        return "Items related to {$name}";
    }

    private function getSubcategoryIcon(string $name): string
    {
        return 'category';
    }

    private function getChildSubcategoryDescription(string $name): string
    {
        return "Items related to {$name}";
    }
}
