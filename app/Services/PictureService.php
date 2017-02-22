<?php

namespace Knowfox\Services;

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

    public function imageDirectory($uuid, $image_name = null)
    {
        return public_path('images') . '/' . str_replace('-', '/', $uuid)
            . ($image_name ? '/' . $image_name : '');
    }

    public function upload(UploadedFile $file, $uuid)
    {
        $directory = $this->imageDirectory(
            $uuid,
            Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        );

        $extension = '.' . $file->guessExtension();
        $filename = 'original' . $extension;

        $file = $file->move($directory, $filename);

        $image = new Imagick($file->getPathname());
        $this->autoRotateImage($image);

        foreach (array_keys(config('styles')) as $style) {
            @unlink($directory . '/' . $style . '.jpeg');
        }

        $path = $directory . '/rotated.jpeg';
        $image->writeImage($path);

        return $path;
    }

    public function image($uuid, $image_name, $style_name)
    {
        $config_prefix = 'styles.' . $style_name;
        $style_width = config($config_prefix . '.width');
        $style_height = config($config_prefix . '.height');

        $path = $this->imageDirectory($uuid, $image_name) . '/rotated.jpeg';

        $image = new Imagick($path);

        $this->thumbnail($image, $style_width, $style_height);
        $image->writeImage(dirname($path) . '/' . $style_name . '.jpeg');

        return new Response($image->getImageBlob(), 200, [
            "Content-Type" => "image/jpeg"
        ]);
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
        $result = [];

        $dir = $this->imageDirectory($uuid);

        $prefix_len = strlen(public_path('images'));

        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if (strpos($entry, '.') === 0) {
                continue;
            }

            $path = $dir . '/' . $entry . '/rotated.jpeg';
            if (file_exists($path)) {
                $result[] = '/images' . substr($path, $prefix_len);
            }
        }
        $d->close();

        return $result;
    }
}