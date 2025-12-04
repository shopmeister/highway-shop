<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Imagick;
use imagickdraw;
use imagickpixel;
use Dde\Picqer\Barcode\Exceptions\BarcodeException;

class TextImageRenderer
{
    protected $useImagick = true;


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        // Auto switch between GD and Imagick based on what is installed
        if (extension_loaded('imagick')) {
            $this->useImagick = true;
        } elseif (function_exists('imagecreate')) {
            $this->useImagick = false;
        } else {
            throw new \Exception('Neither gd-lib or imagick are installed!');
        }
    }

    /**
     * Force the use of Imagick image extension
     */
    public function useImagick()
    {
        $this->useImagick = true;
    }

    /**
     * Force the use of the GD image library
     */
    public function useGd()
    {
        $this->useImagick = false;
    }

    public function renderTextImage(string $text): string
    {
        $foregroundColor = [0, 0, 0];
        if ($this->useImagick) {
            $imagickDraw = new imagickdraw();
            $imagickDraw->setFillColor(new imagickpixel('rgb(' . implode(',', $foregroundColor) . ')'));
            $imagickDraw->annotation(0, 12, $text);
            $image = $this->createImagickImageObject(500, 14);
            $image->drawImage($imagickDraw);
            return $image->getImageBlob();
        } else {
            $image = $this->createGdImageObject(500, 10);
            $gdForegroundColor = imagecolorallocate($image, $foregroundColor[0], $foregroundColor[1], $foregroundColor[2]);
            $black = imagecolorallocate($image, 0, 0, 0);
            imagestring($image, 1, 0, 0, $text, $black);
            ob_start();
            $this->generateGdImage($image);
            return ob_get_clean();
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @return false|\GdImage|resource
     */
    protected function createGdImageObject(int $width, int $height)
    {
        $image = imagecreate($width, $height);
        $colorBackground = imagecolorallocate($image, 255, 255, 255);
        imagecolortransparent($image, $colorBackground);

        return $image;
    }

    /**
     * @param int $width
     * @param int $height
     * @return Imagick
     * @throws \ImagickException
     */
    protected function createImagickImageObject(int $width, int $height): Imagick
    {
        $image = new Imagick();
        $image->newImage($width, $height, 'none', 'PNG');

        return $image;
    }

    /**
     * @param $image
     * @return void
     */
    protected function generateGdImage($image)
    {
        imagepng($image);
        imagedestroy($image);
    }
}
