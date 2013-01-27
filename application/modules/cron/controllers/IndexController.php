<?php
/**
 * IndexController.php
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Date: 16.07.12
 */
class Cron_IndexController extends Core_Controller_Cli
{
    /**
     * @var Deploy_Model_UpdateQueue_Table
     */
    private  $_queueTable;

    public function init()
    {
        $this->msg("Wellcome to Cron Module :)");

        $this->_queueTable = new Deploy_Model_UpdateQueue_Table();

        if ($this->_queueTable->isLockedTasksExists()) {
            $this->msg("Can't process tasks - sandbox is busy", true);
            return;
        }
    }

    /**
     * Just test action
     */
    public function indexAction()
    {
        $this->msg("index");
    }

    /**
     * Check environments
     */
    public function checkEnvironmentsAction()
    {
        $envTable = new Deploy_Model_Environment_Table();

        $envs = $envTable->fetchAll(
            $envTable->select()
        );

        if (!count($envs)) {
            $this->msg('No environments found', true);
            return;
        }

        try {
            foreach ($envs as $env) {
                /**
                 * @var $env Deploy_Model_Environment
                 */
                $parameters = $env->validateAllParameters();

                if ($parameters['isEnvironmentReady']) {
                    $env->setValid();
                    $this->msg("Environment {$env->name} valid ");
                } else {
                    $env->setInvalid();
                    $this->msg("Environment {$env->name} not valid :" . print_r($parameters, true));
                }
            }
        } catch (Exception $e) {
            $this->msg(
                "Error occurred : " . $e->getMessage()
            );
        }
    }

    /**
     *  Cron action for update environments
     */
    public function updateEnvironmentsAction()
    {
        $tasks = $this->_queueTable->getNotProcessedTasks();
        $envManager = new Deploy_Model_Environment_Manager();

        foreach ($tasks as $task) {
            if ($envManager->updateEnvironment($task)) {
                $this->msg("Task " . $task->id . " processed");
            } else {
                $this->msg(
                    "Error occurred on task "
                    . $task->id . ". See task log for more details"
                );
            }
        }
    }
}
