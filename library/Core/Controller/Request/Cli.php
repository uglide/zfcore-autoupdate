<?php
/**
 * Cli.php
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Date: 16.07.12
 */
class Core_Controller_Request_Cli extends Zend_Controller_Request_Simple
{
    public function isPost()
    {
        return false;
    }

    public function getPathInfo()
    {
        return $this->getModuleName() . '/' . $this->getControllerName() . '/' . $this->getActionName();
    }
}
