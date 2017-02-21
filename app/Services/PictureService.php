<?php

namespace Knowfox\Services;

use Imagick;
use Knowfox\Models\FileModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \Illuminate\Http\Response;

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

    public function handleUpload(UploadedFile $file, $destination_path)
    {
        $destination_path .= md5_file($file->getPathname());

        $filename = 'original.' . $file->guessExtension();
        @mkdir($destination_path, 0775, true);
        $file->move($destination_path, $filename);

        $filename = $destination_path . '/' . $filename;

        $image = new Imagick($filename);
        $this->autoRotateImage($image);

        foreach (array_keys(config('styles')) as $style) {
            @unlink($destination_path . '/' . $style . '.jpeg');
        }

        $filename = $destination_path . '/rotated.jpeg';
        $image->writeImage($filename);

        return $filename;
    }

    public function image($path, $style_name)
    {
        $style = config('styles.' . $style_name);
        $image = new Imagick($path);

        $this->autoRotateImage($image);

        $this->thumbnail($image, $style['width'], $style['height']);
        $image->writeImage(dirname($path) . '/' . $style_name . '.jpeg');

        return new Response($image->getImageBlob(), 200, [
            "Content-Type" => "image/jpeg"
        ]);
    }

    public function asset($filename, $style)
    {
        return asset(strtr($filename, [
            'rotated' => $style,
            'original' => $style,
            //'.jpeg' => '.png',
        ]));
    }
}