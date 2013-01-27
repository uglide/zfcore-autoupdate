<?php
class Core_Pdf_Page extends Zend_Pdf_Page
{
    /*
      * If page contains pagebreaks, pages are stored here
      */
    private $_pages = array();

    /**
     * @var
     */
    private $_margin;

    /**
     * @var
     */
    private $_padding;

    /**
     * @var Zend_Pdf_Style
     */
    private $_defaultStyle;

    /**
     * @var null
     */
    public $currPos = null;

    /**
     * Get Default Page Style
     *
     * @return Zend_Pdf_Style
     */
    public function getDefaultStyle()
    {
        return $this->_defaultStyle;
    }

    public function copyPage()
    {
        $copy = new Core_Pdf_Page($this);

        $copy->setFont($this->getFont(), $this->getFontSize());
        $copy->setMargins($this->getMargins());
        $copy->setPaddings($this->getPaddings());

        return $copy;
    }

    /**
     * Get all pages for this page (page overflows)
     *
     * @return array pages
     */
    public function getPages()
    {
        if (count($this->_pages) > 0) {
            return array_merge(array($this), $this->_pages);
        } else {
            return false;
        }
    }

    /**
     * Set page margins
     *
     * @param array(TOP,RIGHT,BOTTOM,LEFT)
     */
    public function setMargins($margin = array())
    {
        $this->_margin = $margin;
    }

    /**
     * Set page paddings
     *
     * @param array(TOP,RIGHT,BOTTOM,LEFT)
     */
    public function setPaddings($paddings = array())
    {
        $this->_padding = $paddings;
    }

    /**
     * Get a Page padding
     *
     * @param int $position
     * @return int margin
     */
    public function getPadding($position)
    {
        if (isset($this->_padding[$position])) {
            return $this->_padding[$position];
        } else {
            return 0;
        }
    }

    /**
     * Get a Page paddings
     * @return mixed
     */
    public function getPaddings()
    {
        return $this->_padding;
    }

    /**
     * Get Page Width
     *
     * @param bool $intContentArea
     * @return int
     */
    public function getWidth($intContentArea = false)
    {
        $width = parent::getWidth();
        if ($intContentArea) {
            $width -= $this->_margin[Core_Pdf::LEFT];
            $width -= $this->_margin[Core_Pdf::RIGHT];
        }

        return $width;
    }

    /**
     * Get a Page margin
     *
     * @param Core_Pdf::Position $position
     * @return int margin
     */
    public function getMargin($position)
    {
        return $this->_margin[$position];
    }

    /**
     * Get Page Margins
     *
     * @return array(TOP,RIGHT,BOTTOM,LEFT)
     */
    public function getMargins()
    {
        return $this->_margin;
    }

    /**
     * Set Page Font
     *
     * @param Zend_Pdf_Resource_Font $font
     * @param int $fontSize
     */
    public function setFont(Zend_Pdf_Resource_Font $font, $fontSize = 10)
    {
        $this->_font = $font;
        $this->_fontSize = $fontSize;
        parent::setFont($font, $fontSize);
    }

    public function __construct($param1, $param2 = null, $param3 = null)
    {
        parent::__construct($param1, $param2, $param3);

        $style = new Zend_Pdf_Style();
        $style->setLineColor(new Zend_Pdf_Color_Html("#000000"));
        $style->setFillColor(new Zend_Pdf_Color_Html("#000000"));
        $style->setLineWidth(0.5);

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
        $style->setFont($font, 10);

        $style->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);

        $this->_defaultStyle = $style;
        $this->setStyle($style);
    }

    /**
     * @param Core_Pdf $doc
     * @param Core_Pdf_Table $table
     * @param $posX
     * @param $posY
     * @param bool $inContentArea
     * @return mixed
     */
    public function addTable(Core_Pdf $doc, Core_Pdf_Table $table, $posX, $posY, $inContentArea = true)
    {
        //render table
        $pages = $table->render($this, $posX, $posY, $inContentArea);

        //check for new pages
        if (is_array($pages))
            $doc->pages = array_merge($doc->pages, $pages);

        return end($doc->pages);
    }

    /**
     * Get text properties (width, height, [#lines using $max Width]), and warps lines
     * @param $text
     * @param null $maxWidth
     * @return array
     */
    public function getTextProperties($text, $maxWidth = null)
    {

        $lines = $this->_textLines($text, $maxWidth);

        return array(
            'text_width' => $lines['text_width'],
            'max_width' => $lines['max_width'],
            'height' => ($this->getFontHeight() * count($lines['lines'])),
            'lines' => $lines['lines']
        );
    }

    /**
     * Draw Text
     * @param $text
     * @param $x
     * @param $y
     * @param string $charEncoding
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawText($text, $x, $y, $charEncoding = "utf-8")
    {
        return parent::drawText($text, $x, $y, $charEncoding);
    }

    /**
     * Draw Rectangle
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @param int $filltype
     * @param bool $inContentArea
     * @return Core_Pdf_Page|Zend_Pdf_Canvas_Interface
     */
    public function drawRectangle($x1, $y1, $x2, $y2,
                                  $filltype = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE,
                                  $inContentArea = false)
    {
        //move origin
        if ($inContentArea) {
            $y1 = $this->getHeight() - $y1 - $this->getMargin(Core_Pdf::TOP);
            $y2 = $this->getHeight() - $y2 - $this->getMargin(Core_Pdf::TOP);
            $x1 = $x1 + $this->getMargin(Core_Pdf::LEFT);
            $x2 = $x2 + $this->getMargin(Core_Pdf::LEFT);
        }

        return parent::drawRectangle($x1, $y1, $x2, $y2, $filltype);
    }

    /**
     * Get Font Height
     *
     * @return int
     */
    public function getFontHeight()
    {
        $line_height = $this->getFont()->getLineHeight();
        $line_gap = $this->getFont()->getLineGap();
        $em = $this->getFont()->getUnitsPerEm();
        $size = $this->getFontSize();
        return ($line_height - $line_gap) / $em * $size;
    }

    /**
     * Returns the with of the text
     *
     * @param string $text
     * @return int $width
     */
    private function _getTextWidth($text)
    {

        $glyphs = array();
        $em = $this->_font->getUnitsPerEm();

        //get glyph for each character
        foreach (range(0, strlen($text) - 1) as $i) {
            $glyphs [] = @ord($text [$i]);
        }

        $width = array_sum($this->_font->widthsForGlyphs($glyphs)) / $em * $this->_fontSize;

        return $width;
    }

    /**
     * Wrap text according to max width
     *
     * @param string $text
     * @param int $maxWidth
     * @return array lines
     */
    private function _wrapText($text, $maxWidth)
    {
        $xInc = 0;
        $currLine = '';
        $words = explode(' ', trim($text));
        $spaceWidth = $this->_getTextWidth(' ');
        $lines = array();
        foreach ($words as $word) {
            //no new line found
            $width = $this->_getTextWidth($word);

            if (isset ($maxWidth) && ($xInc + $width) <= $maxWidth) {
                //add word to current line
                $currLine .= ' ' . $word;
                $xInc += $width + $spaceWidth;
            } else {
                //store current line
                if (strlen(trim($currLine, "\n")) > 0)
                    $lines[] = trim($currLine);

                //new line
                $xInc = 0; //reset position
                $currLine = array(); //reset curr line
                //add word
                $currLine = $word;
                $xInc += $width + $spaceWidth;
            }
        }

        //last line
        if (strlen(trim($currLine, "\n")) > 0) {
            $lines[] = trim($currLine);
        }

        return $lines;
    }

    /**
     * Enter description here...
     *
     * @param string $text
     * @param int $maxWidth (optional, if not set (auto width) the max width is set by reference)
     * @return array line(text);
     */
    private function _textLines($text, $maxWidth = null)
    {
        $trimmedLines = array();
        $textWidth = 0;
        $lineWidth = 0;

        $lines = explode("\n", $text);
        $maxLineWidth = 0;
        foreach ($lines as $line) {
            if (strlen($line) <= 0) continue;
            $lineWidth = $this->_getTextWidth($line);
            if ($maxWidth > 0 && $lineWidth > $maxWidth) {
                $newLines = $this->_wrapText($line, $maxWidth);
                $trimmedLines += $newLines;

                foreach ($newLines as $nline) {
                    $lineWidth = $this->_getTextWidth($nline);
                    if ($lineWidth > $maxLineWidth)
                        $maxLineWidth = $lineWidth;
                }
            } else {
                $trimmedLines[] = $line;
            }
            if ($lineWidth > $maxLineWidth)
                $maxLineWidth = $lineWidth;
        }

        //set actual width of line
        if (is_null($maxWidth))
            $maxWidth = $maxLineWidth;

        $textWidth = $maxLineWidth;

        return array('lines' => $trimmedLines, 'text_width' => $textWidth, 'max_width' => $maxWidth);
    }
}

