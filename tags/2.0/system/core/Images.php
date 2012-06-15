<?php
namespace Helium;

/*
 * Images.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all image manipulation
 */

class Images {
    public $imgSrc, $myImage, $cropHeight, $cropWidth, $x, $y, $thumb, $thumbSize, $originWidth, $originHeight;

    /**
     *
     * function: cropImage
     * Used for cropping an image
     * @access public
     * @param string $image
     * @param double $pct
     * @param int $thumbSize
     * @return null
     */
    function cropImage($image,$pct=.65,$thumbSize=150) {
        $this->thumbSize = $thumbSize;
        $this->imgSrc = $image;
        
        list($width, $height) = getimagesize($this->imgSrc);
        $this->myImage = imagecreatefromjpeg($this->imgSrc) or die('Error: Cannot find image!');

        if ($width < $thumbSize && $height < $thumbSize) {
            imagecopy($this->thumb,$this->myImage,0,0,$width,$height,$width,$height);
            renderImage();
        }

        if ($width > $height) $biggestSide = $width;
        else $biggestSide = $height;

        $this->cropWidth   = $biggestSide*$pct;
        $this->cropHeight  = $biggestSide*$pct;

        $this->originWidth = $width;
        $this->originHeight = $height;

        $this->x = ($width-$this->cropWidth)/2;
        $this->y = 0;

        $this->createThumb();
        $this->renderImage();
    }

    /**
     *
     * function: createThumb
     * Create an image thumbnail
     * @access public
     * @return null
     */
    function createThumb() {
        $thumbSize = $this->thumbSize;
        $this->thumb = imagecreatetruecolor($thumbSize, $thumbSize);

        imagecopyresampled($this->thumb,$this->myImage,0,0,$this->x,$this->y,$thumbSize,$thumbSize,$this->cropWidth,$this->cropHeight);
    }

    /**
     *
     * function: renderImage
     * Render an image
     * @access public
     * @return null
     */
    function renderImage() {
        header('Content-type: image/jpeg');
        imagejpeg($this->thumb);
        imagedestroy($this->thumb);
    }
}

