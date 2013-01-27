<?php

class Core_Pdf_Table_Cell
{

    protected $_width;
    protected $_height;
    protected $_recommendedWidth;
    protected $_recommendedHeight;
    protected $_text;
    protected $_font;
    protected $_fontSize = 10;
    protected $_align;
    protected $_vAlign;
    protected $_bgColor;
    protected $_color;
    protected $_textLineSpacing = 0;

    public $_manualWidth = false;

    protected $_image;

    /**
     * Cell padding
     *
     * @var array (padding-top,padding-right,padding-bottom,padding-left)
     */
    private $_padding;

    /**
     * Cell borders
     *
     * @var array (Core_Pdf position=>array('color','width','dashing_pattern'))
     */
    private $_border;

    /**
     * Set Text Line Height
     *
     * @param int $value
     */
    public function setTextLineSpacing($value)
    {
        $this->_textLineSpacing = $value;
    }

    /**
     * Checks if Cell contains a Image Element
     *
     * @return bool
     */
    public function hasImage()
    {
        if ($this->_image) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if Cell contains a Text Element
     *
     * @return bool
     */
    public function hasText()
    {
        if ($this->_text) {
            return true;
        } else {
            return false;
        }
    }

    public function getRecommendedWidth()
    {
        return $this->_recommendedWidth;
    }

    public function getRecommendedHeight()
    {
        return $this->_recommendedHeight;
    }

    /**
     * Set Cell Background Color
     *
     * @param Zend_Pdf_Color $color
     */
    public function setBackgroundColor(Zend_Pdf_Color $color)
    {
        $this->_bgColor = $color;
    }

    /**
     * Set Cell Text Color
     *
     * @param Zend_Pdf_Color $color
     */
    public function setColor(Zend_Pdf_Color $color)
    {
        $this->_color = $color;
    }

    /**
     * Set Cell Padding
     *
     * @param Core_Pdf $position
     * @param int $value padding
     */
    public function setPadding($position, $value)
    {
        $this->_padding[$position] = $value;
    }

    /**
     * Get Cell Padding
     *
     * @param Core_Pdf $position
     * @return int
     */
    public function getPadding($position)
    {
        if (isset($this->_padding[$position])) {
            return $this->_padding[$position];
        } else {
            return false;
        }
    }

    /**
     * Set Horizontal Alignment
     *
     * @param Core_Pdf $align
     */
    public function setAlignment($align)
    {
        $this->_align = $align;
    }

    /**
     * Get Alienment
     *
     * @return Core_Pdf
     */
    public function getAlignment()
    {
        return $this->_align;
    }

    /**
     * Set Vertical Alignment
     *
     * @param Core_Pdf $align
     */
    public function setVAlignment($align)
    {
        $this->_vAlign = $align;
    }

    public function getVAlignment()
    {
        return $this->_vAlign;
    }

    /**
     * Get Cell Border
     *
     * @param $position
     * @return Zend_Pdf_Style $style
     */
    public function getBorder($position)
    {
        if (isset($this->_border[$position])) {
            return $this->_border[$position];
        } else {
            return false;
        }
    }

    /**
     * Set cell border properties
     *
     * @param Core_Pdf $position
     * @param Zend_Pdf_Style $style
     */
    public function setBorder($position, Zend_Pdf_Style $style)
    {
        $this->_border[$position] = $style;
    }

    /**
     * Set Cell Borders
     *
     * @param array(array(Core_Pdf position, Zend_Pdf_Styles style))
     */
    public function setBorders($borders)
    {
        $this->_border = $borders;
    }

    /**
     * Remove Cell Border
     *
     * @param Core_Pdf $position
     */
    public function removeBorder($position)
    {
        unset($this->_border[$position]);
    }

    /**
     * Set Font and Size
     *
     * @param Zend_Pdf_Resource_Font $font
     * @param int $fontSize
     */
    public function setFont(Zend_Pdf_Resource_Font $font, $fontSize = 10)
    {
        $this->_font = $font;
        $this->_fontSize = $fontSize;
    }

    /**
     * Get Cell Font
     *
     * @return Zend_Pdf_Resource_Font
     */
    public function getFont()
    {
        return $this->_font;
    }

    /**
     * Set Cell Width
     * @param $value
     * @param bool $manualWidth
     */
    public function setWidth($value, $manualWidth = false)
    {
        $this->_width = $value;

        if ($manualWidth)
            $this->_manualWidth = $manualWidth;
    }

    /**
     * Get Cell Width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Set Cell Height
     *
     * @param int $value
     */
    public function setHeight($value)
    {
        $this->_height = $value;
    }

    /**
     * Get Cell Height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Add text to cell
     * @param $text
     * @param null $align
     * @param null $valign
     * @return Core_Pdf_Table_Cell
     */
    public function setText($text, $align = null, $valign = null)
    {
        $this->_text['text'] = $text;
        if ($align)
            $this->_align = $align;
        if ($valign)
            $this->_vAlign = $valign;

        return $this;
    }

    /**
     * Get Cell Text Element
     *
     * @return array(text,width,lines)
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Replace Text String in Text-Element
     *
     * @param mixed $search
     * @param mixed $replace
     * @return int number of replaced strings
     */
    public function replaceText($search, $replace)
    {
        $numReplaced = 0;
        $text = str_replace($search, $replace, $this->_text['text'], $numReplaced);
        $this->_text['text'] = $text;
        return $numReplaced;
    }

    /**
     * @param $filename
     * @param null $align
     * @param null $vAlign
     * @param int $scale
     * @throws Zend_Exception
     */
    public function setImage($filename, $align = null, $vAlign = null, $scale = 1)
    {
        $this->_image['filename'] = $filename;

        if ($scale > 1)
            throw new Zend_Exception("Scale must be between (0,1]", "Core_Pdf_Table_Cell::addImage()");
        $this->_image['scale'] = $scale;

        $this->_align = $align;
        $this->_vAlign = $vAlign;
    }

    /**
     * Pre-render cell to get recommended width and height
     * @param Core_Pdf_Page $page
     * @param $posX
     * @param $posY
     * @param bool $inContentArea
     * @throws Zend_Exception
     */
    public function preRender(Core_Pdf_Page $page, $posX, $posY, $inContentArea = true)
    {
        if (!$this->_width) {
            //no width given, get max width of page
            if ($inContentArea) {
                $width = $page->getWidth() - $posX -
                    ($page->getMargin(Core_Pdf::LEFT) + $page->getMargin(Core_Pdf::RIGHT));
            } else {
                $width = $page->getWidth() - $posX;
            }
        } else {
            $width = $this->_width;
        }

        //calc max cell width
        $maxWidth = $width -
            ($this->_padding[Core_Pdf::LEFT] + $this->_padding[Core_Pdf::RIGHT]) -
            (+$this->_getBorderLineWidth(Core_Pdf::LEFT) + $this->_getBorderLineWidth(Core_Pdf::RIGHT));

        if ($this->_text) {

            //set font
            $page->setFont($this->_font, $this->_fontSize);

            //get height,width,lines
            $textProps = $page->getTextProperties($this->_text['text'], $maxWidth);

            //reset style
            $page->setStyle($page->getDefaultStyle());

            //set width
            if (!$this->_width) {
                //add padding
                $this->_recommendedWidth = $textProps['text_width'] +
                    ($this->_padding[Core_Pdf::LEFT] + $this->_padding[Core_Pdf::RIGHT]) +
                    $this->_getBorderLineWidth(Core_Pdf::LEFT) + $this->_getBorderLineWidth(Core_Pdf::RIGHT);
            } else {
                $this->_recommendedWidth = $textProps['max_width'];
            }

            if (!$this->_height) {
                //set height, add padding
                if ($this->_textLineSpacing) {
                    $height = $this->_textLineSpacing * count($textProps['lines']) + $textProps['height'];
                } else {
                    $height = $textProps['height'];
                }
                $this->_recommendedHeight = $height +
                    ($this->_padding[Core_Pdf::TOP] + $this->_padding[Core_Pdf::BOTTOM]);
            }

            //store text props;
            $this->_text['width'] = $textProps['text_width'];
            $this->_text['max_width'] = $textProps['max_width'];
            $this->_text['height'] = $textProps['height'];
            $this->_text['lines'] = $textProps['lines'];
        } elseif ($this->_image) {

            $image = Zend_Pdf_Image::imageWithPath($this->_image['filename']);

            if (!$this->_width)
                $this->_recommendedWidth = $this->_image['scale'] * $image->getPixelWidth() +
                    ($this->_padding[Core_Pdf::LEFT] + $this->_padding[Core_Pdf::RIGHT]) +
                    $this->_getBorderLineWidth(Core_Pdf::LEFT) + $this->_getBorderLineWidth(Core_Pdf::RIGHT);
            if (!$this->_height)
                $this->_recommendedHeight = $this->_image['scale'] * $image->getPixelHeight() +
                    ($this->_padding[Core_Pdf::TOP] + $this->_padding[Core_Pdf::BOTTOM]);

            $this->_image['image'] = $image;
            $this->_image['width'] = $this->_image['scale'] * $image->getPixelWidth();
            $this->_image['height'] = $this->_image['scale'] * $image->getPixelHeight();
        } else {
            throw new Zend_Exception("not defined", "preRender()");
        }
    }

    /**
     * Render Cell
     *
     * @param Core_Pdf_Page $page
     * @param int $posX
     * @param int $posY
     */
    public function render(Core_Pdf_Page $page, $posX, $posY)
    {
        $this->_renderBackground($page, $posX, $posY);
        $this->_renderText($page, $posX, $posY);
        $this->_renderImage($page, $posX, $posY);
        $this->_renderBorder($page, $posX, $posY);
    }

    private function _renderText(Core_Pdf_Page $page, $posX, $posY)
    {
        if (!$this->_text) return;

        $page->setFont($this->_font, $this->_fontSize);

        if ($this->_color)
            $page->setFillColor($this->_color);

        if (count($this->_text['lines']) > 1) {

            $lineHeight = $page->getFontHeight() + $this->_textLineSpacing;
            $yInc = $posY - $this->_textLineSpacing;
            $this->_vAlign = Core_Pdf::TOP;
            foreach ($this->_text['lines'] as $line) {
                $page->drawText($line, $this->_getTextPosX($posX), $this->_getTextPosY($page, $yInc));
                $yInc -= $lineHeight;
            }
        } else {
            //write single line of text
            $page->drawText($this->_text['text'], $this->_getTextPosX($posX), $this->_getTextPosY($page, $posY));
        }
        //reset style
        $page->setStyle($page->getDefaultStyle());
    }

    private function _renderImage(Core_Pdf_Page $page, $posX, $posY)
    {
        if (!$this->_image) return;

        $page->drawImage(
            $this->_image['image'],
            $this->_getImagePosX($posX),
            $this->_getImagePosY($posY),
            $this->_image['width'],
            $this->_image['height']
        );

    }

    private function _renderBorder(Core_Pdf_Page $page, $posX, $posY)
    {
        if (!$this->_border) return;

        foreach ($this->_border as $key => $style) {
            $page->setStyle($style);
            switch ($key) {
                case Core_Pdf::TOP:
                    $page->drawLine(
                        $posX, $posY + $this->_getBorderLineWidth(Core_Pdf::TOP) / 2,
                        $posX + $this->_width, $posY + $this->_getBorderLineWidth(Core_Pdf::TOP) / 2,
                        true
                    );
                    break;
                case Core_Pdf::BOTTOM:
                    $y = $posY - $this->_height + $this->_getBorderLineWidth(Core_Pdf::BOTTOM) / 2;
                    $page->drawLine(
                        $posX, $y,
                        $posX + $this->_width, $y,
                        true
                    );
                    break;
                case Core_Pdf::RIGHT:
                    //@@TODO INCLUDE BORDER LINE WIDTH??
                    $page->drawLine(
                        $posX + $this->_width, $posY,
                        $posX + $this->_width, $posY - $this->_height,
                        true
                    );
                    break;
                case Core_Pdf::LEFT:
                    //@@TODO INCLUDE BORDER LINE WIDTH??
                    $page->drawLine(
                        $posX, $posY,
                        $posX, $posY - $this->_height,
                        true
                    );
                    break;
            }
            //reset page style
            $page->setStyle($page->getDefaultStyle());
        }
    }

    private function  _renderBackground(Core_Pdf_Page $page, $posX, $posY)
    {
        if (!$this->_bgColor) return;
        $page->setFillColor($this->_bgColor);
        $page->drawRectangle(
            $posX,
            $posY,
            $posX + $this->_width,
            $posY + $this->_height,
            ZEND_PDF_PAGE::SHAPE_DRAW_FILL
        );

        //reset style
        $page->setStyle($page->getDefaultStyle());
    }

    /**
     * Positions text horizontally (x-axis) adding alignment
     * Default alignment: LEFT
     * @param int $posX
     * @return int
     */
    private function _getTextPosX($posX)
    {
        $x = 0;
        switch ($this->_align) {
            case Core_Pdf::RIGHT:
                $x = $posX + $this->_width - $this->_text['width'] -
                    $this->_padding[Core_Pdf::RIGHT] - $this->_getBorderLineWidth(Core_Pdf::RIGHT) / 2;
                break;
            case Core_Pdf::CENTER:
                $x = $posX + $this->_width / 2 - $this->_text['width'] / 2;
                break;
            default: //LEFT
                $x = $posX + $this->_padding[Core_Pdf::LEFT] + $this->_getBorderLineWidth(Core_Pdf::LEFT) / 2;
                break;
        }
        return $x;
    }

    /**
     * Positions text vertically (y-axis) adding vertical alignment
     * Default alignment: TOP
     * @param Core_Pdf_Page $page
     * @param int $posY
     * @return int
     */
    private function _getTextPosY(Core_Pdf_Page $page, $posY)
    {
        $y = 0;
        $lineHeight = $page->getFontHeight() + $this->_textLineSpacing;

        switch ($this->_vAlign) {
            case Core_Pdf::BOTTOM:
                $y = $posY + $this->_height - $this->_padding[Core_Pdf::BOTTOM];
                break;
            case Core_Pdf::MIDDLE:
                $y = $posY + $this->_height / 2 + $lineHeight / 2;
                break;
            default: //TOP
                $y = $posY - $lineHeight - $this->_padding[Core_Pdf::TOP];
                break;
        }
        return $y;
    }


    private function _getImagePosX($posX)
    {
        $x = 0;
        switch ($this->_align) {
            case Core_Pdf::RIGHT:
                $x = $posX + $this->_width - $this->_image['width'] - $this->_padding[Core_Pdf::RIGHT];
                break;
            case Core_Pdf::CENTER:
                $x = $posX + $this->_width / 2 - $this->_image['width'] / 2;
                break;
            default: //LEFT
                $x = $posX + $this->_padding[Core_Pdf::LEFT];
                break;
        }
        return $x;
    }

    private function _getImagePosY($posY)
    {
        $y = 0;
        switch ($this->_vAlign) {
            case Core_Pdf::BOTTOM:
                $y = $posY + $this->_height - $this->_image['height'] - $this->_padding[Core_Pdf::BOTTOM];
                break;
            case Core_Pdf::MIDDLE:
                $y = $posY + ($this->_height - $this->_image['height']) / 2;
                break;
            default: //TOP
                $y = $posY + $this->_padding[Core_Pdf::TOP];
                break;
        }
        return $y;
    }

    private function _getBorderLineWidth($position)
    {
        if (isset($this->_border[$position])) {
            $style = $this->_border[$position];
            $width = $style->getLineWidth();
        } else {
            $width = 0;
        }
        return $width;
    }

}

