<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait HandleUploads
{
    public static function uploadImage($image, $folder)
    {
        $filename = '_' . Str::uuid() . time() . '.' . $image->getClientOriginalExtension();
        $image->move($folder, $filename);
        return $folder . '/' . $filename;
    }

    public static function uploadImages($images, $folder)
    {
        $filenames = [];
        foreach ($images as $image) {
            $filename = '_' . Str::uuid() . time() . '.' . $image->getClientOriginalExtension();
            $image->move($folder, $filename);
            $filenames[] = $folder . '/' . $filename;
        }
        return $filenames;
    }

    public static function deleteImages($paths)
    {
        foreach ($paths as $path) {
            self::deleteImage($path);
        }
    }

    public static function deleteImage($path)
    {
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}


