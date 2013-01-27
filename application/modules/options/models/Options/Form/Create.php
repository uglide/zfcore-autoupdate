<?php
/**
 * Register user form
 *
 * @category Application
 * @package Debug
 * @subpackage Options
 *
 * @author Anna Pavlova <pavlova.anna@nixsolutions.com>
 *
 * @version  $Id$
 */
class Options_Model_Options_Form_Create extends Zend_Form
{
    protected $_inputDecorators = array(
        array('HtmlTag', array('tag' => 'dd', 'class'=>'control-group')),
        array('Label', array('tag' => 'dt', 'class'=>'control-group')),
        array('Errors', array('class'=>'help-inline')),
    );

    /**
     * Maximum of name length
     * @var integer
     */
    const MAX_NAME_LENGTH = 32;

    /**
     * Maximux of value length
     * @var integer
     */
    const MAX_VALUE_LENGTH = 1024;

    /**
     * Maximum of namespace length
     * @var integer
     */
    const MAX_NAMESPACE_LENGTH = 32;

    /**
     * Form initialization
     *
     * @return void
     */
    public function init()
    {
        $this->setName('optionCreateForm')
                ->setMethod('post');

        $this->addElements(
            array($this->_namespace(),
                 $this->_name(),
                 $this->_type(),
                 $this->_value(),
                 $this->_submit()
            )
        );
        return $this;
    }

    /**
     * Create name element
     *
     * @return object Zend_Form_Element_ValidationTextBox
     */
    protected function _name()
    {
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
             ->addDecorators($this->_inputDecorators)
                ->setRequired(true)
                ->setAttribs(array('class' => 'span3'))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('regex', false, array('/([a-zA-Z0-9 _-])+/'))
                ->addValidator(
                    'StringLength',
                    false,
                    array(
                        0,
                        self::MAX_NAME_LENGTH
                    )
                );

        return $name;
    }

    /**
     * Create value element
     *
     * @return object Zend_Form_Element_ValidationTextBox
     */
    protected function _value()
    {
        $value = new Zend_Form_Element_Text('value');
        $value->setLabel('Value')
                ->addDecorators($this->_inputDecorators)
                ->setRequired(true)
                ->setAttribs(array('class' => 'span3'))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator(
                    'StringLength',
                    false,
                    array(
                         0,
                        self::MAX_VALUE_LENGTH
                    )
                );

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $type = $request->getParam('type');
        if (!empty($type)) {
            if ($type == Options_Model_Options_Manager::TYPE_INT) {
                $value->addValidator('int', false);
            }
            if ($type == Options_Model_Options_Manager::TYPE_FLOAT) {
                $value->addValidator('float', false);
            }
        }

        return $value;
    }

    /**
     * Create type element
     *
     * @return object Zend_Form_Element_ValidationTextBox
     */
    protected function _type()
    {
        $value = new Zend_Form_Element_Select('type');
        $value->setLabel('Type')
                ->addDecorators($this->_inputDecorators)
                ->setRequired(true)
                ->setAttribs(array('class' => 'span3'))
                ->setMultiOptions(
                    array(
                         Options_Model_Options_Manager::TYPE_ARRAY =>
                         Options_Model_Options_Manager::TYPE_ARRAY,
                         Options_Model_Options_Manager::TYPE_FLOAT =>
                         Options_Model_Options_Manager::TYPE_FLOAT,
                         Options_Model_Options_Manager::TYPE_INT =>
                         Options_Model_Options_Manager::TYPE_INT,
                         Options_Model_Options_Manager::TYPE_OBJECT =>
                         Options_Model_Options_Manager::TYPE_OBJECT,
                         Options_Model_Options_Manager::TYPE_STRING =>
                         Options_Model_Options_Manager::TYPE_STRING
                    )
                );
        return $value;
    }

    /**
     * Create namespace element
     *
     * @return object Zend_Form_Element_ValidationTextBox
     */
    protected function _namespace()
    {
        $namespace = new Zend_Form_Element_Text('namespace');
        $namespace->setLabel('Namespace')
                ->addDecorators($this->_inputDecorators)
                ->setRequired(true)
                ->setAttribs(array('class' => 'span3'))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator(
                    'regex',
                    false,
                    array('/([a-zA-Z0-9 _-])+/')
                )
                ->addValidator(
                    'StringLength',
                    false,
                    array(
                         0,
                        self::MAX_NAMESPACE_LENGTH
                    )
                );
        return $namespace;
    }

    /**
     * Create submit element
     *
     * @return object Zend_Form_Element_ValidationTextBox
     */
    protected function _submit()
    {
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Create');
        $submit->setAttrib('class', 'btn btn-primary');

        return $submit;
    }
}