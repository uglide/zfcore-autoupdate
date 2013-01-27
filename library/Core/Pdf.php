<?php

/**
 * Core_PDF
 * updated by Igor Malinovskiy <u.glide@gmail.com>.
 * based on: http://sourceforge.net/projects/zendpdftable/
 */

class Core_Pdf extends Zend_Pdf{
	
	const TOP=0;
	const RIGHT=1;
	const BOTTOM=2;
	const LEFT=3;
	const CENTER=4;	//horizontal center
	const MIDDLE=5; //vertical center

    public static function load($source = null, $revision = null)
    {
        return new Core_Pdf($source, $revision, true);
    }

    /**
     * Load pages recursively
     *
     * @param Zend_Pdf_Element_Reference $pages
     * @param array|null $attributes
     */
    protected function _loadPages(Zend_Pdf_Element_Reference $pages, $attributes = array())
    {
        if ($pages->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            throw new Zend_Pdf_Exception('Wrong argument');
        }

        foreach ($pages->getKeys() as $property) {
            if (in_array($property, self::$_inheritableAttributes)) {
                $attributes[$property] = $pages->$property;
                $pages->$property = null;
            }
        }

        foreach ($pages->Kids->items as $child) {
            if ($child->Type->value == 'Pages') {
                $this->_loadPages($child, $attributes);
            } else if ($child->Type->value == 'Page') {
                foreach (self::$_inheritableAttributes as $property) {
                    if ($child->$property === null && array_key_exists($property, $attributes)) {
                        /**
                         * Important note.
                         * If any attribute or dependant object is an indirect object, then it's still
                         * shared between pages.
                         */
                        if ($attributes[$property] instanceof Zend_Pdf_Element_Object  ||
                            $attributes[$property] instanceof Zend_Pdf_Element_Reference) {
                            $child->$property = $attributes[$property];
                        } else {
                            $child->$property = $this->_objFactory->newObject($attributes[$property]);
                        }
                    }
                }

                $this->pages[] = new Core_Pdf_Page($child, $this->_objFactory);
            }
        }
    }
}
?>