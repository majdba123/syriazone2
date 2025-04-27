<?php

namespace App\Services\SubCategory;

use App\Models\Sub_Categort;

class SubCategoryService
{
    public function getAll()
    {
        return Sub_Categort::with('attribute')->get();
    }

    public function getById($id)
    {
        return Sub_Categort::with('attribute')->get(); // يرجع الخطأ 404 إذا لم يتم العثور
    }

    public function store(array $data)
    {
        $subcategory = Sub_Categort::create($data);

        // Create attributes
        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attributeData) {
                $subcategory->attribute()->create([
                    'name' => $attributeData['name'],
                    'sub__categort_id' => $subcategory->id
                ]);
            }
        }

        return $subcategory->load('attribute');
    }

    public function update(Sub_Categort $subcategory, array $data)
    {
        $subcategory->update($data);

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attributeData) {
                $existingAttribute = $subcategory->attribute()
                    ->where('id', $attributeData['attribute_id'])
                    ->first();

                if ($existingAttribute) {
                    $existingAttribute->update(['name' => $attributeData['name']]);
                }
            }
        }

        return $subcategory->load('attribute');
    }

    public function delete(Sub_Categort $subcategory)
    {
        return $subcategory->delete();
    }


    public function get_by_category_id($id)
    {
        return Sub_Categort::where('category_id' ,$id)->get(); // يرجع الخطأ 404 إذا لم يتم العثور
    }
}
