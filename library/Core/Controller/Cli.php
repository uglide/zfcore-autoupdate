<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Cli.php
 * Date: 10.08.12
 */
abstract class Core_Controller_Cli extends Zend_Controller_Action
{
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }

    protected function msg($text, $exit = false)
    {
        echo $text . PHP_EOL;

        if ($exit) {
            exit();
        }
    }

}
