<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * UpdateController.php
 * Date: 21.12.12
 */
class Deploy_UpdateController extends Core_Controller_Action
{
    /**
     * @var Deploy_Model_Environment_Table
     */
    private $_envTable;

    public function init()
    {
        $this->_envTable = new Deploy_Model_Environment_Table();
    }

    public function indexAction()
    {
        $this->view->environments = $this->_envTable->getAllGroupedByType();

        /**
         * Set current user to view
         */
        $this->view->user = Zend_Auth::getInstance()->getIdentity();

        $this->view->verifiedVersions = null;

        $manager = new Deploy_Model_Version_Manager();

        $this->view->verifiedVersions = array(
            Deploy_Model_Environment::TYPE_STAGE =>
                $manager->getVerifiedVersions(Deploy_Model_Environment::TYPE_STAGE),
            Deploy_Model_Environment::TYPE_LIVE =>
            $manager->getVerifiedVersions(Deploy_Model_Environment::TYPE_LIVE)
        );

    }
}
