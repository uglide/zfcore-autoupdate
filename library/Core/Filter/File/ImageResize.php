<?php
/**
 * Image resize filter
 * Based on ZFEngine_Image
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 */
class Core_Filter_File_ImageResize implements Zend_Filter_Interface
{
    /**
     * Options
     * @var array
     */
    private $_options = array(
        'width' => 0,
        'height' => 0,
        'quality' => 100,
        'saveProportions' => false
    );

    /**
     * Set options
     *
     * @param array|string|Zend_Config $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_int($options)) {
            $options = array('width' => $options);
        } elseif (!is_array($options)) {
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }

        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * Resize image
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (!file_exists($value)) {
            return $value;
        }

        $image = Core_Image::factory($value);
        $image->resizeWithBackground($this->_options['width'], $this->_options['height'])
            ->save();

        return $value;
    }
}