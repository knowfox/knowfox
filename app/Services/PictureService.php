<?php

namespace Knowfox\Services;

use Illuminate\Http\File;
use Imagick;
use Knowfox\Models\FileModel;
use Knowfox\Models\Concept;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \Illuminate\Http\Response;
use Illuminate\Support\Str;

use DOMDocument;
use DOMXpath;
use Exception;

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
                $this->thumbnail($image, (int)$args[0], null);
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

    /**
     * @param $el \DOMElement
     */
    private function getUuid($el)
    {
        while (($el = $el->parentNode) && !$el->hasAttribute('data-uuid'));
        return $el->getAttribute('data-uuid');
    }

    public function extractPictures($markup, $target_directory)
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

            $target_path = $target_directory . '/' . $filename;
            $source_path = $this->imageDirectory($uuid) . '/' . $filename;
            file_put_contents(
                $target_path,
                $this->imageData($source_path, $style, $args)
            );

            $image->setAttribute('src', $filename);
        }

        $text = "<!DOCTYPE html>\n<html>\n";
        $html = $dom->getElementsByTagName('html')->item(0);
        foreach ($html->childNodes as $node) {
            $text .= $dom->saveHTML($node);
        }
        $text .= "\n</html>";

        return $text;
    }
}