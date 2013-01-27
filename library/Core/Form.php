<?php
/**
 * Form prepared for work with ZFCore+Bootstrap
 *
 * @category Core
 * @package  Form
 *
 * @author   dark
 * @created  10.04.12 16:38
 */
class Core_Form extends Zend_Form
{

    public function __construct($options = null)
    {
        parent::__construct($options);

        //Adding unique hash - CSRF protection to every form in application
        $securityOptions = Zend_Registry::getInstance()->get('security');

        if (!$securityOptions) {
            throw new Core_Exception(
                "Security options not exists in application.yaml!"
            );
        }

        if ($securityOptions['csrfProtection']) {
            $this->addElement($this->_csrfToken());
        }
    }

    //filters for trim user data
    protected $_filters = array(
        array(
            'filter' => 'PregReplace',
            'options' => array(
                'match' => '/(  )+/i',
                'replace' => ' '
            )
        ),
        array(
            'filter' => 'StringTrim'
        )
    );

    /**
     * @var array
     */
    protected $_inputDecorators = array(
        array(
            'HtmlTag',
            array('tag' => 'dd', 'class'=>'control-group form-inline')
        ),
        array('Label', array('tag' => 'dt', 'class'=>'control-group')),
        array('Errors', array('class'=>'help-inline')),
    );

    /**
     * @var string
     */
    protected $_placeholder = 'Type here...';

    /**
     * Create submit element
     *
     * @return Zend_Form_Element_Submit
     */
    protected function _submit()
    {
        $element = new Zend_Form_Element_Submit('submit');
        $element->setLabel('Save');
        $element->setAttrib('class','btn btn-primary span2');
        $element->setOrder(100);

        return $element;
    }

    /**
     * @param array $options
     * @return Core_Form_Element_PlainText
     */
    protected function _cancel(array $options = null)
    {
        $element = new Core_Form_Element_Cancel('cancel');
        $element->setDecorators(array(
            'ViewHelper',
            'Errors',
            array('Description', array('tag' => 'p', 'class' => 'description')),
            'Label'
        ));
        $element->setPath()->setValue();
        return $element;
    }

    /**
     * @return Zend_Form_Element_Captcha
     */
    protected function _reCaptcha()
    {
        $config = Zend_Registry::get('recaptchaConfig');

        // check captcha path is writeable
        $element = new Zend_Form_Element_Captcha(
            'captcha',
            array(
                'label' => "Please verify you're a human",
                'captcha' => 'reCaptcha',
                'captchaOptions' => array(
                    'captcha' => 'reCaptcha',
                    'privKey' => $config['privKey'],
                    'pubKey' => $config['pubKey'],
                    'theme' => 'white'
                ),
            )
        );
        return $element;
    }

    /**
     * @return Zend_Form_Element_Captcha|Zend_Form_Element_Hidden
     */
    protected function _captcha()
    {
        $registry = Zend_Registry::getInstance();
        if (!isset($registry['captcha'])
        || (isset($registry['captcha']) && $registry['captcha'])) {
            $imgUrl = '/captcha';
            $imgDir = PUBLIC_PATH . $imgUrl;

            // check captcha path is writeable
            if (is_writable($imgDir)) {
                $element = new Zend_Form_Element_Captcha(
                    'captcha',
                    array(
                        'label' => "Please verify you're a human",
                        'captcha' => 'Image',
                        'captchaOptions' => array(
                            'captcha' => 'Image',
                            'wordLen' => 4,
                            'timeout' => 300,
                            'imgDir' => $imgDir,
                            'imgUrl' => $imgUrl,
                            'font' => dirname(APPLICATION_PATH) .
                                "/data/fonts/Aksent_Normal.ttf",
                            //'fontSize' => 30,
                            'dotNoiseLevel' => 25,
                            'lineNoiseLevel' => 2,
                            'height' => 70,
                        ),
                    )
                );
            } else {
                $element = new Zend_Form_Element_Captcha(
                    'captcha',
                    array(
                        'label' => "Please verify you're a human",
                        'captcha' => 'Figlet',
                        'captchaOptions' => array(
                            'wordLen' => 4,
                            'timeout' => 300,
                        ),
                    )
                );
            }
            $element->clearDecorators()
                ->addDecorator(
                    'HtmlTag',
                    array('tag' => '<div>', 'class' => 'captcha l')
                )
                ->addDecorator('Label')
                ->addDecorator(
                    'Description',
                    array(
                        'tag' => '<button>',
                        'class' => 'css-btn css-btn-gradient captcha-refresh l',
                        'title' => 'Press to reload image',
                    )
                )
                ->setDescription('Reload')
                ->addDecorator('Errors');
        } else {
            $element = new Zend_Form_Element_Hidden('captcha');
        }

        return $element;

    }

    /**
     * @return Zend_Form_Element_Hash
     */
    protected function _csrfToken()
    {
        $uniqueSalt = Zend_Crypt::hash('MD5', 'csrf' . microtime());

        $element = new Zend_Form_Element_Hash(
            'csrf_token_' . strtolower(get_class($this))
        );
        $element->setSalt($uniqueSalt);
        $element->addDecorators($this->_inputDecorators);

        return $element;
    }

    /**
    * @return Core_Form
    * @return Zend_Form_Element_Captcha
    */
    protected function _setPlaceholders()
    {
        foreach ($this->getElements() as $element) {
            if ($element instanceof Zend_Form_Element_Text
            || $element instanceof Zend_Form_Element_Password) {
                if (!$element->getAttrib('placeholder')) {
                    $element->setAttrib('placeholder', $this->_placeholder);
                }
            }
        }
        return $this;
    }

    /**
     * @return Zend_Validate_Regex
     */
    protected function _allnumHyphenWhitespaceValidator()
    {
        $validator = new Zend_Validate_Regex(
            array(
                'pattern' => '/^[a-z]+(?:[-\s][a-z]+)*$/i'
            )
        );
        return $validator;
    }
}
