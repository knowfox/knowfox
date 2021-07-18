<?php

namespace Knowfox\Services;

use Illuminate\Http\File;
use Imagick;
use Knowfox\Models\FileModel;
use Knowfox\Models\Concept;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \Illuminate\Http\Response;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;

use DOMDocument;
use DOMXpath;
use Exception;

class PictureService
{
    private $upload_fs;

    public function __construct()
    {
        $this->upload_fs = env('UPLOAD_FS', 'local');
    }
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

    public function dirs($flat)
    {
        $path = '';
        $len = strlen($flat);
        
        // Split the first 8 characters into directories
        for ($i = 0; $i < min(8, $len); $i += 2) {
            $path .= '/' . substr($flat, $i, 2);
        }

        // Append the remaining characters
        if ($i < $len) {
            $path .= '/' . substr($flat, $i);
        }
        return $path;
    }

    public function imageDirectory($uuid)
    {
        $path = '';
        if ($this->upload_fs == 'local') {
            $path .= storage_path('uploads');
        }
        else {
            $path .= $this->dirs(str_replace('-', '', $uuid));
	    }
	    return $path;
    }

    public function upload(UploadedFile $file, $uuid)
    {
        $directory = $this->imageDirectory($uuid);
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . $file->guessExtension();

        if ($this->upload_fs == 'local') {
            $file = $file->move($directory, $filename);
            $path = $file->getPathname();
        }
        else {
            $path = $file->storeAs(
                $directory, $filename, 
                'upload' // disk name
            );
        }

        if (strpos($file->getMimeType(), 'image') === 0) {
            $image = $this->imagick($path);
            $this->autoRotateImage($image);

            if ($this->upload_fs == 'local') {
                $image->writeImage($path);
            }
            else {
                Storage::disk('upload')->put($path, 
                    $image->getImageBlob());
            }
        }

        return $path;
    }

    public function imagick($path)
    {
        if ($this->upload_fs == 'local') {
            $image = new Imagick($path);
        }
        else {
            $image = new Imagick();
            $image->readImageBlob(
                Storage::disk('upload')->get($path));
        }
        return $image;
    }

    public function imageData($path, $style_name, $args = [])
    {
        $image = $this->imagick($path);

        switch ($style_name) {
            case 'original':
                break;

            case 'width':
                $this->thumbnail($image, (int)$args[0], null);
                break;

            default:
                $config_prefix = 'knowfox.styles.' . $style_name;
                $style_width = config($config_prefix . '.width');
                $style_height = config($config_prefix . '.height');

                $this->thumbnail($image, $style_width, $style_height);
        }

        return $image->getImageBlob();
    }

    public function mimeType($path)
    {
        if ($this->upload_fs == 'local') {
            $file = new File($path);
            $type = $file->getMimeType();
        }
        else {
            $type = Storage::disk('upload')->mimeType($path);
        }
        return $type;
    }

    public function path($uuid, $filename)
    {
        return $this->imageDirectory($uuid) . '/' . $filename;
    }

    public function image($uuid, $filename, $style_name, $args)
    {
        $path = $this->path($uuid, $filename);
        $type = $this->mimeType($path);

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
            if ($this->upload_fs == 'local') {
                $blob = file_get_contents($path);
            }
            else {
                $blob = Storage::disk('upload')->get($path);
            }
            return new Response($blob, 200, [
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

    public function withStyle($path, $style)
    {
        $info = pathinfo($path);

        $result = '';
        if ($info['dirname'] && $info['dirname'] != '.') {
            $result .= $info['dirname'] . '/';
        }
        return $result . $info['filename'] . '-' . $style . '.' . $info['extension'];
    }

    public function images($uuid)
    {
        $images = [];

        $dir = $this->imageDirectory($uuid);

        $images = Storage::disk('upload')->files($dir);
        if (!$images) {
            return [];
        }
        
        return collect($images)->map(function ($item) {
            return basename($item);
        });
    }

    /**
     * @param $el \DOMElement
     */
    private function getUuid($el)
    {
        while (($el = $el->parentNode) && !$el->hasAttribute('data-uuid'));
        return $el->getAttribute('data-uuid');
    }

    public function extractPictures($markup, $target_directory, $wrapped = true)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;

        // @see http://de1.php.net/manual/en/domdocument.loadhtml.php
        if (!$dom->loadHTML('<?xml version="1.0" encoding="UTF-8" ?>' . $markup)) {
            $messages = [];
            foreach(libxml_get_errors() as $error) {
                $messages[] = $error->message;
            }
            throw new Exception("XML: " . join(', ', $messages));
        }

        $xpath = new DOMXpath($dom);

        foreach (iterator_to_array($xpath->query('//img')) as $i => $image) {
            $url = parse_url(trim($image->getAttribute('src')));
            if (!empty($url['host']) && $url['host'] != 'knowfox.com') {
                continue;
            }

            if (preg_match('#^(/concept)?/(\d+)/(.*)#', $url['path'], $matches)) {
                $uuid = Concept::find($matches[2])->uuid;
                $filename = $matches[3];
            }
            else {
                $uuid = $this->getUuid($image);
                $filename = $url['path'];
            }

            $style = 'original';
            $args = [];
            if (!empty($url['query'])) {
                $query = [];
                parse_str($url['query'], $query);

                if (isset($query['width'])) {
                    $style = 'width';
                    $args[] = $query['width'];
                }
                else
                    if (isset($query['style'])) {
                        $style = $query['style'];
                    }
            }

            $source_path = $this->path($uuid, $filename);
            copy($source_path, $target_directory . '/' . $filename);

            $suffix = $style;
            if ($args) {
                $suffix .= '-' . join('-', $args);
            }
            $filename = $this->withStyle($filename, $suffix);

            $target_path = $target_directory . '/' . $filename;
            file_put_contents(
                $target_path,
                $this->imageData($source_path, $style, $args)
            );

            $image->setAttribute('src', $filename);
        }

        $text = '';
        $html = $dom->getElementsByTagName($wrapped ? 'html' : 'body')->item(0);
        if ($html && $html->childNodes) {
            foreach ($html->childNodes as $node) {
                $text .= $dom->saveHTML($node);
            }
        }

        if ($wrapped) {
            return "<!DOCTYPE html>\n<html>\n" . $text . "\n</html>";
        }
        else {
            return $text;
        }
    }
}
