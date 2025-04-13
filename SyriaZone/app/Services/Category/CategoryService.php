<?php
namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data)
    {
        $category->update($data);
        return $category;
    }

    public function delete(Category $category)
    {
        return $category->delete();
    }

    public function getAll()
    {
        // جلب جميع الفئات مع الـ sub_category الخاص بها
        return Category::with('sub_category')->get();
    }

    public function getById($id)
    {
        // جلب الفئة المحددة مع الـ sub_category الخاص بها
        return Category::with('sub_category')->findOrFail($id);
    }


}
