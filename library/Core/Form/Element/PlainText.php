<?php
/**
 * Plain Text Form Element
 *
 * @category Core
 * @package  Core_Form
 * @subpackage Element
 *
 * @author Dmitriy Savchenko <savchenko.d.v@nixsolutions.com>
 * @date: 22.06.12
 */
class Core_Form_Element_PlainText extends Zend_Form_Element_Xhtml
{
    /**
     * View Helper
     * @var string
     */
    public $helper = 'PlainTextElement';

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        return true;
    }
}
