<?php
/**
 * PNG image adapter
 * Based on ZFEngine_Image
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 */
class Core_Image_Adapter_Png extends Core_Image_Adapter_Abstract
{
    public function  __construct($filename)
    {
        $this->_filename = $filename;
        $this->_image = imagecreatefrompng($this->_filename);

        imagealphablending($this->_image, false);
        imagesavealpha($this->_image, true);

        return $this;
    }

    public function save($quality = 100)
    {
        $result = imagepng($this->_image, $this->_filename, self::_quality($quality));

        if ($result === true) {
            return $this;
        }

        throw new Core_Exception(
            sprintf(
                "File '%s' could not be saved. An error occured while processing the file.",
                $this->_filename
            )
        );
    }

    private static function _quality($quality) {
        return 9 - round($quality * 9 / 100);
    }

    protected function _imageCreate($width, $height)
    {
        if (function_exists('imagecreatetruecolor')) {
            $image = imagecreatetruecolor($width, $height);

            imagealphablending($image, false);
            imagesavealpha($image, true);

            // Create a new transparent color for image
            $bgcolor = imagecolorallocatealpha($image, 0, 0, 0, 127);
            // Completely fill the background of the new image with allocated color.
            imagefilledrectangle($image, 0, 0, $width, $width, $bgcolor);
        } else {
            $image = imagecreate($width, $height);
        }

        return $image;
    }

}
