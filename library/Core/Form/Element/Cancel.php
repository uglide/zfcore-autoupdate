<?php
/**
 * Cancel Form Element
 *
 * @category Core
 * @package  Core_Form
 * @subpackage Element
 *
 * @author Dmitriy Savchenko <savchenko.d.v@nixsolutions.com>
 * @date: 22.06.12
 */
class Core_Form_Element_Cancel extends Core_Form_Element_PlainText
{
    /**
     * @var bool
     */
    protected $_ignore = true;

    /**
     * @var array
     */
    protected $_path = null;

    /**
     * View Helper
     * @var string
     */
    public $helper = 'PlainTextElement';

    /**
     * @param null $value
     * @return void|Zend_Form_Element
     */
    public function setValue($value = null)
    {
        if (!$value) {
            $path = $this->getPath();
            if (empty($path)) {
                $this->setPath();
                $path = $this->getPath();
            }
            $value = $this->_getValue($path);
        }
        parent::setValue($value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        return true;
    }

    /**
     * @param $path
     * @return string
     */
    protected function _getValue($path)
    {
        $url = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('view')
            ->getHelper('url')
            ->url($path[0], $path[1], $path[2]);
        $html = "<a href='$url' id='cancel' class='btn btn-inverse pull-right'>Cancel</a>";
        return $html;
    }

    /**
     * @param array $path
     * @return Core_Form_Element_Cancel
     */
    public function setPath(array $path = null)
    {
        $this->_path = array();
        $this->_path = $this->getPath();
        $this->_path = empty($this->_path) ? $this->getDefaultPath() : $this->_path;

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if (isset($path[0]) && is_array($path[0])) {
            foreach($path[0] as $key => $value) {
                if (is_numeric($key)) {
                    $this->_path[0][$value] = $request->getParam($value);
                } else {
                    $this->_path[0][$key] = $value;
                }
            }
        }
        $this->_path[1] = isset($path[1]) && is_string($path[1]) ? $path[1] : $this->_path[1];
        $this->_path[2] = isset($path[2]) ? $path[2] : $this->_path[2];
        return $this;
    }

    /**
     * @return array
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @return array
     */
    public function getDefaultPath()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $pathParams = array(
            'module' => $request->getModuleName(),
            'controller' => $request->getControllerName(),
            'action' => 'index',
        );

        return array(
            $pathParams,
            'default',
            true
        );
    }
}
