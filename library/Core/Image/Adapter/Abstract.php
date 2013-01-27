<?php
/**
 * Abstract image adapter
 * Based on ZFEngine_Image
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 */
abstract class Core_Image_Adapter_Abstract
{
    // Master Dimension
    const NONE = 1;
    const AUTO = 2;
    const HEIGHT = 3;
    const WIDTH = 4;

    protected $_filename;

    protected $_image;

    abstract public function  __construct($filename);

    public function  __destruct()
    {
        imagedestroy($this->_image);
    }

    /**
     * Save to current file
     * @abstract
     * @param int $quality
     */
    abstract public function save($quality = 100);

    /**
     * Save to another file
     * @param $filename
     * @param int $quality
     * @return Core_Image_Adapter_Abstract
     */
    public function saveAs($filename, $quality = 100)
    {
        $this->_filename = $filename;
        $this->save($quality);

        return $this;
    }

    /**
     * @param $maxWidth
     * @param int $maxHeight
     * @param bool $saveProportions
     * @param null $ratioType
     * @return Core_Image_Adapter_Abstract
     * @throws Exception
     */
    public function resize($maxWidth, $maxHeight = 0, $saveProportions = false, $ratioType = NULL)
    {
        if (min($maxWidth, $maxHeight) < 0) {
            throw new Core_Exception('Invalid image size');
        }

        if (($maxWidth && $maxHeight) && ($this->width <= $maxWidth && $this->height <= $maxHeight)) {
            return $this;
        }

        if ($maxHeight == 0) {
            $saveProportions = true;
        }

        if ($saveProportions) {
            $ratioWidth = $this->width / $maxWidth;
            $ratioHeight = ($maxHeight > 0) ? ($this->height / $maxHeight) : $ratioWidth;
            switch ($ratioType) {
                case self::HEIGHT:
                    $ratio = $ratioHeight;
                    break;
                case self::WIDTH:
                    $ratio = $ratioWidth;
                    break;
                case self::AUTO:
                default:
                    $ratio = max($ratioWidth, $ratioHeight);
                    break;
            }

            $dstWidth = round($this->width / $ratio);
            $dstHeight = round($this->height / $ratio);

            $srcWidth = $this->width;
            $srcHeight = $this->height;

            $srcX = 0;
            $srcY = 0;
        } else {

            $ratio = min(($this->width / $maxWidth),
                ($this->height / $maxHeight));

            $dstWidth = $maxWidth;
            $dstHeight = $maxHeight;

            $srcWidth = round($maxWidth * $ratio);
            $srcHeight = round($maxHeight * $ratio);
            $srcX = round(($this->width - $srcWidth) / 2);
            $srcY = round(($this->height - $srcHeight) / 2);
        }

        $image = $this->_imageCreate($dstWidth, $dstHeight);

        imagecopyresampled($image, $this->_image,
            0, 0,
            $srcX, $srcY,
            $dstWidth, $dstHeight,
            $srcWidth, $srcHeight);

        $this->_image = $image;

        return $this;
    }

    /**
     * @param $degrees
     * @return Core_Image_Adapter_Abstract
     */
    public function rotate($degrees)
    {
        $this->_image = imagerotate($this->_image, $degrees, -1);
        imagealphablending($this->_image, true);
        imagesavealpha($this->_image, true);

        return $this;
    }

    public function __get($name)
    {
        switch($name) {
            case 'width':
                return imagesx($this->_image);
                break;
            case 'height':
                return imagesy($this->_image);
                break;
            case 'fileName':
                return basename($this->fullPath);
                break;
            case 'fullPath':
                return $this->_filename;
                break;
            case 'resourse':
                return $this->_image;
                break;
        }
    }


    /**
     * @param $watermarkFileName
     * @param float $rate
     * @return Core_Image_Adapter_Abstract
     */
    public function addWatermark($watermarkFileName, $rate = 0.05)
    {
        $watermark = Core_Image::factory($watermarkFileName);
        $watermark->resize($this->width * $rate, $this->height * $rate, true, 100);

        $startX = ($this->width - 5) - $watermark->width;
        $startY = ($this->height - 5) - $watermark->height;
        imagecopy($this->_image, $watermark->resourse,
            $startX, $startY,
            0, 0,
            $watermark->width, $watermark->height);

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return resource
     */
    protected function _imageCreate($width, $height)
    {
        if (function_exists('imagecreatetruecolor')) {
            $image = imagecreatetruecolor($width, $height);
            $bgcolor = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $bgcolor);
        } else {
            $image = imagecreate($width, $height);
        }

        return $image;
    }

    /**
     * @param $width
     * @param $height
     * @param array $color
     * @return Core_Image_Adapter_Abstract
     */
    public function resizeWithBackground($width, $height, $color = array(255, 255, 255))
    {
        $srcWidth = $this->width;
        $srcHeight = $this->height;

        $proportion = $height / $width;
        $imageProportion = $srcHeight / $srcWidth;

        $orginalIsLess = ($srcWidth < $width && $srcHeight < $height) ? true : false;


        if ($imageProportion == $proportion && !$orginalIsLess) {
            return $this->resize($width, $height, true);
        } else {

            $image = $this->_imageCreate($width, $height, $color);

            if ($orginalIsLess) {

                $dstWidth = $srcWidth;
                $dstHeight = $srcHeight;

                $dstX = round(($width - $srcWidth) / 2);
                $dstY = round(($height - $srcHeight) / 2);

            } elseif ($imageProportion > $proportion) {

                $ratio = $height / $srcHeight;

                $dstWidth = round($srcWidth * $ratio);
                $dstHeight = $height;

                $dstX = round(($width - $dstWidth) / 2);
                $dstY = 0;

            } elseif ($imageProportion < $proportion) {

                $ratio = $width / $srcWidth;

                $dstWidth = $width;
                $dstHeight = round($srcHeight * $ratio);

                $dstX = 0;
                $dstY = round(($height - $dstHeight) / 2);
            }

            imagecopyresampled(
                $image, $this->_image, $dstX, $dstY, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight
            );

            $this->_image = $image;

            return $this;
        }
    }

}