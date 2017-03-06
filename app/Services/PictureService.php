<?php

namespace Knowfox\Services;

use Illuminate\Http\File;
use Imagick;
use Knowfox\Models\FileModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \Illuminate\Http\Response;
use Illuminate\Support\Str;

class PictureService
{
    public function autoRotateImage(Imagick $image)
    {
        $orientation = $image->getImageOrientation();

        switch($orientation) {
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateImage("#000", 180); // rotate 180 degrees
                break;

            case Imagick::ORIENTATION_RIGHTTOP:
                $image->rotateImage("#000", 90); // rotate 90 degrees CW
                break;

            case Imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateImage("#000", -90); // rotate 90 degrees CCW
                break;
        }

        /* Now that it's auto-rotated, make sure the EXIF data is correct
         * in case the EXIF gets saved with the image
         */
        $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
    }

    public function thumbnail(Imagick $image, $new_w, $new_h)
    {
        $w = $image->getImageWidth();
        $h = $image->getImageHeight();

        if (empty($new_w)) {
            $resize_w = $w * $new_h / $h;
            $resize_h = $new_h;
        }
        else
        if (empty($new_h)) {
            $resize_w = $new_w;
            $resize_h = $h * $new_w / $w;
        }
        else
        if ($w > $h) {
            $resize_w = $w * $new_h / $h;
            $resize_h = $new_h;
        }
        else {
            $resize_w = $new_w;
            $resize_h = $h * $new_w / $w;
        }

        $image->resizeImage($resize_w, $resize_h, Imagick::FILTER_LANCZOS, 0.9);
    }

    public function imageDirectory($uuid)
    {
        return storage_path('uploads') . '/' . str_replace('-', '/', $uuid);
    }

    public function upload(UploadedFile $file, $uuid)
    {
        $directory = $this->imageDirectory($uuid);
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . $file->guessExtension();

        $file = $file->move($directory, $filename);
        $path = $file->getPathname();

        $image = new Imagick($path);
        $this->autoRotateImage($image);

        $image->writeImage($path);

        return $path;
    }

    public function imageData($path, $style_name, $args = [])
    {
        $image = new Imagick($path);

        switch ($style_name) {
            case 'original':
                break;

            case 'width':
                $this->thumbnail($image, $args[0], null);
                break;

            default:
                $config_prefix = 'styles.' . $style_name;
                $style_width = config($config_prefix . '.width');
                $style_height = config($config_prefix . '.height');

                $this->thumbnail($image, $style_width, $style_height);
        }

        return $image->getImageBlob();
    }

    public function image($uuid, $filename, $style_name, $args)
    {
        $path = $this->imageDirectory($uuid) . '/' . $filename;
        $file = new File($path);
        $type = $file->getMimeType();

        if (strpos($type, 'image/') === 0) {
            return new Response($this->imageData($path, $style_name, $args), 200, [
                "Content-Type" => "image/jpeg",
            ]);
        }
        else
        // Image preview in edit form
        if ($style_name == 'h80') {
            $sub_type = preg_replace('#^[^/]*/#', '', $type);
            return new Response('<?xml version="1.0" encoding="UTF-8" ?><svg xmlns="http://www.w3.org/2000/svg"  width="80" height="80" fill="red"><text text-anchor="middle" x="40" y="40">'
                . $sub_type . '</text></svg>', 200, [
                "Content-Type" => 'image/svg+xml',
            ]);
        }
        else {
            return new Response(file_get_contents($path), 200, [
                "Content-Type" => $type,
            ]);
        }
    }

    public function asset($path, $style)
    {
        return asset(strtr($path, [
            'rotated' => $style,
            'original' => $style,
            //'.jpeg' => '.png',
        ]));
    }

    public function images($uuid)
    {
        $images = [];

        $dir = $this->imageDirectory($uuid);

        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if (strpos($entry, '.') === 0) {
                continue;
            }
            $images[] = $entry;
        }
        $d->close();

        return $images;
    }
}