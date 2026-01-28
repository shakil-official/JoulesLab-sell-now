<?php

namespace SellNow\Services\Product;

use App\Core\Config\Helper;
use App\Core\Services\AuthService;
use SellNow\Models\Product;

class ProductService
{
    public function create($title, $price): bool
    {
        try {
            $uploadDir = __DIR__ . '/../../../public/uploads/';
            $imagePath = Helper::uploadFile('image', $uploadDir);
            $filePath = Helper::uploadFile('product_file', $uploadDir, 'dl');

            $slug = Helper::slug($title);

            Product::create([
                'user_id' => AuthService::userId(),
                'title' => $title,
                'slug' => $slug,
                'price' => $price,
                'image_path' => $imagePath,
                'file_path' => $filePath,
            ]);
        }catch (\Exception $exception){
            return false;
        }

        return true;
    }

}