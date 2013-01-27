<?php

/**
 * Core_PDF
 * updated by Igor Malinovskiy <u.glide@gmail.com>.
 * based on: http://sourceforge.net/projects/zendpdftable/
 */

class Core_Pdf_Document extends Core_Pdf
{

    /*
      * Margin (margin-top,margin-right,margin-bottom,margin-left)
      */
    private $_margin = array(30, 20, 30, 20);
    private $_headerYOffset = 10; //y offset from page top
    private $_footerYOffset = 10; //y offset from margin-bottom --> page bottom
    private $_header;
    private $_footer;
    private $_filename = "document.pdf";
    private $_path = "/";

    /**
     * Set Document Margin
     *
     * @param integer $value
     * @param Core_Pdf $position
     */
    public function setMargin($position, $value)
    {
        $this->_margin[$position] = $value;
    }

    /**
     * Get Document Margins
     *
     * @return array(TOP,RIGHT,BOTTOM,LEFT)
     */
    public function getMargins()
    {
        return $this->_margin;
    }

    /**
     * Set Footer
     *
     * @param Core_Pdf_Table $table
     */
    public function setFooter(Core_Pdf_Table $table)
    {
        $this->_footer = $table;
    }

    /**
     * Set Header
     *
     * @param Core_Pdf_Table $table
     */
    public function setHeader(Core_Pdf_Table $table)
    {
        $this->_header = $table;
    }

    public function __construct($filename, $path)
    {
        $this->_filename = $filename;
        $this->_path = $path;
        parent::__construct();
    }

    /**
     * Create a new Page for this Document
     * Sets all default values (margins,...)
     * @param mixed $param
     * @return Core_Pdf_Page
     */
    public function createPage($param = Zend_Pdf_Page::SIZE_A4)
    {
        $page = new Core_Pdf_Page($param);
        $page->setMargins($this->_margin);
        return $page;
    }

    /**
     * Add Page to this Document
     *
     * @param Core_Pdf_Page $page
     */
    public function addPage(Core_Pdf_Page $page)
    {
        //add pages with new pages (page breaks)
        if ($pages = $page->getPages()) {
            foreach ($pages as $p) {
                $p->setMargins($this->_margin);
                $this->pages[] = $p;
            }
        } else {
            $page->setMargins($this->_margin);
            $this->pages[] = $page;
        }
    }

    /**
     * (renders) and Saves the Document to the specified File
     * 
     * FIXME Method is not compatible with parent, there should be another wrapper class for this
     * 
     */
    public function save()
    {
        //add header/footer to each page
        $i = 1;
        foreach ($this->pages as $page) {
            $this->_drawFooter($page, $i);
            $this->_drawHeader($page, $i);
            $i++;
        }

        parent::save("{$this->_path}/{$this->_filename}");
    }

    private function _drawFooter(Core_Pdf_Page $page, $currentPage)
    {
        if (!$this->_footer) return;
        if ($page instanceof Core_Pdf_Page) {

            //set table width
            $currFooter = clone $this->_footer;
            //check for special place holders
            $rows = $currFooter->getRows();
            foreach ($rows as $key => $row) {
                $row->setWidth($page->getWidth() - $this->_margin[Core_Pdf::LEFT] - $this->_margin[Core_Pdf::RIGHT]);
                $cols = $row->getColumns();
                $num = 0;
                foreach ($cols as $col) {
                    if ($col->hasText()) {
                        $num += $col->replaceText('@@CURRENT_PAGE', $currentPage);
                        $num += $col->replaceText('@@TOTAL_PAGES', count($this->pages));
                    }
                }

                if ($num > 0) {
                    $row->setColumns($cols);
                    $currFooter->replaceRow($row, $key);
                }

            }

            //add table
            $page->addTable(
                $currFooter,
                $this->_margin[Core_Pdf::LEFT],
                ($page->getHeight() - $this->_margin[Core_Pdf::BOTTOM] -
                    $this->_margin[Core_Pdf::TOP] + $this->_footerYOffset),
                false
            );
        }

    }

    private function _drawHeader(Core_Pdf_Page $page, $currentPage)
    {
        if (!$this->_header) return;

        if ($page instanceof Core_Pdf_Page) {

            //set table width
            $currHeader = clone $this->_header;

            //check for special place holders
            $rows = $currHeader->getRows();
            foreach ($rows as $key => $row) {
                $row->setWidth($page->getWidth() - $this->_margin[Core_Pdf::LEFT] - $this->_margin[Core_Pdf::RIGHT]);
                $cols = $row->getColumns();
                $num = 0;
                foreach ($cols as $col) {
                    if ($col->hasText()) {
                        $num += $col->replaceText('@@CURRENT_PAGE', $currentPage);
                        $num += $col->replaceText('@@TOTAL_PAGES', count($this->pages));
                    }
                }

                if ($num > 0) {
                    $row->setColumns($cols);
                    $currHeader->replaceRow($row, $key);
                }

            }


            $page->addTable(
                $currHeader,
                $this->_margin[Core_Pdf::LEFT],
                +$this->_headerYOffset - $this->_margin[Core_Pdf::TOP],
                false
            );
        }
    }
}
