<?php
/**
 * FormImageSelect.php
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Date: 23.05.12
 */
class Core_View_Helper_FormImageSelect extends  Zend_View_Helper_FormSelect
{

    public function formImageSelect($name, $value = null, $attribs = null,
                               $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);

        $id = $this->view->escape($info['id']);

        /** add plugin libraries */
        $this->view->plugins()->imageSelect();

        $comboOptions = array();

        if (!empty($attribs['combo'])) {
            $comboOptions = array_merge($comboOptions, $attribs['combo']);
            unset($attribs['combo']);
        }

        /** init plugin */
        $comboOptions = Zend_Json::encode($comboOptions, Zend_Json::TYPE_OBJECT);
        $this->view->headScript()
            ->appendScript('(function($){$(function(){$("#' . $id . '").combobox(' . $comboOptions . ');});})(jQuery)');

        return parent::formSelect($name, $value, $attribs, $options, $listsep);
    }
}
