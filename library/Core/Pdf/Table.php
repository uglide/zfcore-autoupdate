<?php
class Core_Pdf_Table
{
    private $_width;
    private $_autoWidth = true;
    private $_font;
    private $_fontSize;
    private $_headerFont;
    private $_headerFontSize;

    private $_rows;
    private $_headerRow;
    private $_numColumns;
    private $_pages; //spanning pages or this table
    private $_repeatHeader = true;

    /**
     * Set Table Width
     *
     * @param int $val
     */
    public function setWidth($val)
    {
        $this->_autoWidth = false;
        $this->_width = $val;
    }

    /**
     * Get Table Width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    public function __construct($numColumns)
    {
        $this->_numColumns = $numColumns;

        //set fonts
        $this->_font = Zend_Pdf_Font::FONT_COURIER;
        $this->_fontSize = 10;
        $this->_headerFont = Zend_Pdf_Font::FONT_COURIER;
        $this->_headerFontSize = 12;
    }


    /**
     * Render Table
     * @param Core_Pdf_Page $page
     * @param $posX
     * @param $posY
     * @param bool $inContentArea
     * @return mixed
     */
    public function render(Core_Pdf_Page $page, $posX, $posY, $inContentArea = true)
    {

        if ($this->_headerRow && $this->_rows) {
            //set header as first row
            $this->_rows = array_merge($this->_headerRow, $this->_rows);
        } elseif ($this->_headerRow) {
            //no rows in this table, just the header
            $this->_rows = $this->_headerRow;
        }

        if ($inContentArea) {
            $startY = $posY + $page->getMargin(Core_Pdf::TOP);
            $maxY = $page->getHeight() - $page->getMargin(Core_Pdf::BOTTOM) - $page->getMargin(Core_Pdf::TOP);
        } else {
            $startY = $posY;
            $maxY = $page->getHeight();
            $posX -= $page->getMargin(Core_Pdf::LEFT);
        }


        $y = $startY;

        //prerender
        $this->_preRender($page, $posX, $posY, $inContentArea);
        foreach ($this->_rows as $row) {
            //check current position (height)
            $test = ($y - $row->getHeight());

            if ($test < $page->getPadding(Core_Pdf::BOTTOM) || $row->hasPageBreak()) {

                $page = $page->copyPage();

                $this->_pages[] = $page;
                $y = $page->getHeight() - $page->getPadding(Core_Pdf::TOP);

                if ($this->_headerRow && $this->_repeatHeader) {
                    $header = $this->_rows[0]; //pre-rendered header row (is first row)
                    $header->render($page, $posX, $y);
                    $y -= $header->getHeight() + $header->getBorderLineWidth(Core_Pdf::BOTTOM);
                }
            }

            $row->render($page, $posX, $y);
            $y -= $row->getHeight() + $row->getBorderLineWidth(Core_Pdf::BOTTOM);
            $page->currPos = $y;
        }

        return $this->_pages;
    }

    /**
     * Add Header Row
     *
     * @param Core_Pdf_Table_Row $row
     */
    public function  setHeader(Core_Pdf_Table_Row $row)
    {
        if (!$this->_autoWidth)
            $row->setWidth($this->_width);

        $this->_headerRow[] = $row;
    }

    /**
     * Add Row
     *
     * @param Core_Pdf_Table_Row $row
     */
    public function addRow(Core_Pdf_Table_Row $row)
    {
        //add default row properites if non are set (font/color/size,...)
        //set width
        if (!$this->_autoWidth)
            $row->setWidth($this->_width);

        $this->_rows[] = $row;
    }

    /**
     * Replace specific Row in Table
     *
     * @param Core_Pdf_Table_Row $row
     * @param int $index
     */
    public function replaceRow(Core_Pdf_Table_Row $row, $index)
    {
        if (!$this->_autoWidth)
            $row->setWidth($this->_width);

        $this->_rows[$index] = $row;
    }

    /**
     * Get all Rows in this Table
     *
     * @return array(Core_Pdf_Table_Rows)
     */
    public function getRows()
    {
        return $this->_rows;
    }

    public function __clone()
    {
        foreach ($this as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

    /**
     * Pre-Render Table
     *
     * @param Core_Pdf_Page $page
     * @param int $posX
     * @param int $posY
     * @param bool $inContentArea
     */
    private function _preRender(Core_Pdf_Page $page, $posX, $posY, $inContentArea = true)
    {
        //get auto-colum widths
        $colWidths = array();
        foreach ($this->_rows as $row) {
            //check for colspan's
            $newDumCoreCells = array();
            foreach ($row->getColumns() as $idx => $col) {
                $colWidths[$idx] = $col->getWidth(); //store widht ->for dummy cells
                if ($col->getColspan() > 1) {
                    //insert new cell, for each spanning column
                    $newDumCoreCells[$idx] = $col;
                }
            }

            //insert dummy cells
            foreach ($newDumCoreCells as $idx => $col) {
                for ($i = 1; $i < $col->getColspan(); $i++) {
                    //new col
                    $nCol = new Core_Pdf_Table_Column();
                    $nCol->setText('');
                    if (isset($colWidths[$idx + 1]))
                        $nCol->setWidth($colWidths[$idx + 1]);

                    $row->insertColumn($nCol, $idx + 1);
                }
            }

            //pre-render row
            $row->preRender($page, $posX, $posY, $inContentArea);
            $posY += $row->getHeight() + $row->getBorderLineWidth(Core_Pdf::BOTTOM);
        }

        //set max col width
        $maxColWidth = array();
        foreach ($this->_rows as $row) {
            //get columns max width
            $maxColWidth = array();
            foreach ($row->getColumns() as $idx => $col) {
                $width = $col->getWidth();
                if (!isset($maxColWidth[$idx]) || $width > $maxColWidth[$idx])
                    $maxColWidth[$idx] = $width;
            }
        }

        //set uniform column widht for all rows

        foreach ($this->_rows as $row) {
            foreach ($row->getColumns() as $idx => $col) {
                if (!$col->_manualWidth) {
                    $col->setWidth($maxColWidth[$idx]);
                }
            }
        }

    }
}

