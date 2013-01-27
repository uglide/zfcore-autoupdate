<?php
/**
 * Image resize factory
 * Based on ZFEngine_Image
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Date: 22.05.12
 */
class Core_Image
{
    /**
     * @static
     * @param $filename
     * @return Core_Image_Adapter_Abstract
     * @throws Core_Exception
     */
    public static function factory($filename)
    {
        $size = getimagesize($filename);
        if ($size === false) {
            throw new Core_Exception(sprintf('Image \'%s\' broken', $filename));
        }

        switch ($size['mime']) {
            case 'image/jpeg':
                $adapterName = 'Core_Image_Adapter_Jpg';
                break;

            case 'image/gif':
                $adapterName = 'Core_Image_Adapter_Gif';
                break;

            case 'image/png':
                $adapterName = 'Core_Image_Adapter_Png';
                break;

            default:
                throw new Core_Exception('Unsupported image type');
                break;
        }

        //Create instance
        $imageAdapter = new $adapterName($filename);

        if (!$imageAdapter instanceof Core_Image_Adapter_Abstract) {
            throw new Core_Exception('Invalid image adapter');
        }

        return $imageAdapter;
    }
}