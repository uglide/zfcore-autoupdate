<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Environments.php
 * Date: 29.10.12
 */
class Deploy_EnvironmentsController extends Core_Controller_Action
{
    /**
     * @var Deploy_Model_Environment_Table
     */
    private $_envTable;

    public function init()
    {
        $this->_envTable = new Deploy_Model_Environment_Table();
    }

    /**
     * Page with environments list
     */
    public function indexAction()
    {
        $this->view->environments = $this->_envTable->getAll();
    }

    /**
     * Page with detailed environment info
     * Main action of the app :)
     */
    public function viewAction()
    {
        if (!$id = $this->_getParam('id')) {
            return $this->_forwardNotFound('Environment not found!');
        }

        /**
         * Get environment
         * @var $environment Deploy_Model_Environment
         */
        $environment = $this->view->env = $this->_envTable->getById($id);

        /**
         * Get updates history for environment
         */
        $updatesTable = new Deploy_Model_UpdateQueue_Table();
        $this->view->history = $updatesTable->getHistoryForEnvironment($id);
        $this->view->stats = Zend_Json::encode($updatesTable->getStats($id));

        /**
         * Check environment state
         */
        $this->view->errors = $environment->validateAllParameters();

        /**
         * Set current user to view
         */
        $this->view->user = Zend_Auth::getInstance()->getIdentity();

        $this->view->verifiedVersions = null;

        if ($environment->isLive() || $environment->isStage()) {
            $manager = new Deploy_Model_Version_Manager();

            $this->view->verifiedVersions =
                $manager->getVerifiedVersions($environment->type);
        }

    }

    /**
     * @return mixed
     */
    public function verifyAction()
    {
        $versionID = $this->_getParam('version-id');

        if (!$versionID) {
            return $this->_helper->json(false);
        }

        $manager = new Deploy_Model_Version_Manager();

        return $this->_helper->json(
            $manager->verifyVersion(
                $versionID, Zend_Auth::getInstance()->getIdentity()->id
            )
        );
    }

    /**
     * Ajax action
     */
    public function getEnvironmentStateAction()
    {
        if (!$id = $this->_getParam('id')) {
            return $this->_helper->json(
                'Provide environment ID'
            );
        }

        /**
         * Get environment
         * @var $environment Deploy_Model_Environment
         */
        $environment = $this->_envTable->getById($id);

        $updateTask = $environment->getVersionWaitingForUpdate();

        if ($updateTask) {
            return $this->_helper->json(
                array(
                     'state' => $updateTask->state
                )
            );

        } else {
            return $this->_helper->json(
                array(
                    'state' => 'idle'
                )
            );
        }
    }


    /**
     * Update button handler
     * @return mixed
     */
    public function addUpdateTaskAction()
    {
        $envID = $this->_getParam('envID');
        $targetRevision = trim($this->_getParam('targetVersion'));
        $currentUser = Zend_Auth::getInstance()->getIdentity();

        /**
         * @var $env Deploy_Model_Environment
         */
        if (!$envID
            || !$targetRevision
            || !$currentUser
            || !($env = $this->_envTable->getById($envID))
            || !($env->validateAllParameters()['isEnvironmentReady'])
        ) {
            return $this->_resp(false);
        }

        $queueTable = new Deploy_Model_UpdateQueue_Table();

        if ($queueTable->getLastNotProcessedTask($envID)) {
            return $this->_resp('Environment already has update task');
        }

        try {
            /**
             * Get target version
             */
            $versionsTable = new Deploy_Model_Version_Table();

            if($version = $versionsTable->getByRevisionAndEnvironmentid($targetRevision, $envID)) {
                $targetVersionID = $version->id;
            } else {
                $version = $versionsTable->createRow(
                    array(
                        'revision' => $targetRevision,
                        'environmentID' => $envID
                    )
                );
                $targetVersionID = $version->save();
            }

            //check target version
            $versionsManager = new Deploy_Model_Version_Manager();
            if ($env->isLive()
                && !$versionsManager->isRevisionVerifiedForEnvironment(
                    $targetRevision, Deploy_Model_Environment::TYPE_LIVE
                )) {
                return $this->_resp("Target revision not verified!");
            }

            $currVersion = $versionsTable->getByRevisionAndEnvironmentid(
                $env->getRealVersionInPath(), $envID
            );

            if ($currVersion) {
                $currVersionID = $currVersion->id;
            } else {
                $currVersionID = null;
            }

            $taskID = $queueTable->addTask(
                $envID,
                $currentUser->id,
                $targetVersionID,
                $currVersionID
            );
        } catch (Exception $ex) {
            return $this->_resp('Error : ' . $ex->getMessage());
        }

        return $this->_resp('Update task added', $taskID);
    }


    /**
     * Short alias for json helper
     * @param $msg
     * @param null $status
     * @return mixed
     */
    private function _resp($msg, $status = null)
    {
        return $this->_helper->json(
            array(
                'msg' => $msg,
                'status' => $status
            )
        );
    }
}
