<?php
/**
 * IndexController for admin module
 *
 * @category   Application
 * @package    Dashboard
 * @subpackage Controller
 */
class Admin_IndexController extends Core_Controller_Action
{
    public function init()
    {
        /* Initialize */
        parent::init();

        /* is Dashboard Controller */
        $this->_useDashboard();
    }

    public function indexAction()
    {

    }
}
