<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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

    public static function uploadVideo($video, $folder)
    {
        $filename = 'video_' . Str::uuid() . time() . '.' . $video->getClientOriginalExtension();
        $video->move($folder, $filename);
        return $folder . '/' . $filename;
    }

    public static function uploadVideos($videos, $folder)
    {
        $paths = [];
        $publicPath = public_path($folder);
        foreach ($videos as $video) {
            $filename = 'video_' . Str::uuid() . '.' . $video->getClientOriginalExtension();
            $path = $folder . '/' . $filename;
            $fullPath = $publicPath . '/' . $filename;

            // Using streams for better memory management
            $source = fopen($video->getRealPath(), 'r');
            $destination = fopen($fullPath, 'w');

            stream_copy_to_stream($source, $destination);

            fclose($source);
            fclose($destination);

            $paths[] = $path;
        }

        return $paths;
    }


    public static function deleteVideos($paths)
    {
        foreach ($paths as $path) {
            self::deleteVideo($path);
        }
    }

    public static function deleteVideo($path)
    {
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}


