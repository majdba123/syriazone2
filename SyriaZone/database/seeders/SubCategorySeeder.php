<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sub_Categort;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sub_Categort::create([
            'category_id' => 1,
            'name' => 'SubCategory 1',
        ]);

        Sub_Categort::create([
            'category_id' => 1,
            'name' => 'SubCategory 2',
        ]);

        Sub_Categort::create([
            'category_id' => 2,
            'name' => 'SubCategory 1',
        ]);

        Sub_Categort::create([
            'category_id' => 2,
            'name' => 'SubCategory 2',
        ]);

        // أضف المزيد من البيانات حسب الحاجة
    }
}
