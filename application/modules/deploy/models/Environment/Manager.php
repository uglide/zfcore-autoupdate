<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Manager.php
 * Date: 12.11.12
 *
 * todo: Replace array $log by normal logger implementation
 * todo: Refactor errors checking
 *
 */
class Deploy_Model_Environment_Manager extends Core_Model_Manager
{
    /**
     * @param Deploy_Model_UpdateTask $task
     *
     * @return bool|mixed
     */
    public function updateEnvironment(Deploy_Model_UpdateTask $task)
    {
        /**
         * Lock task
         */
        $task->lock();

        $startTime = microtime(true);

        /**
         * @var Deploy_Model_Environment $env
         */
        $env = $this->getDbTable()->getById($task['environmentID']);
        $repo = $env->getRepo();

        $log = array();

        if (!$this->_prepareRepo($task, $repo, $log)) {
            return $task->setError($this->_logToStr($log));
        }

        $sandboxLog = array();

        if (!$this->_testUpdateInSandBox($task, $env, $sandboxLog)) {
            $log = $this->_addSeparatorToLog($log);
            $log[] = "Error occurred in sandbox :" . $this->_logToStr($sandboxLog);
            $log = $this->_addSeparatorToLog($log);
            return $task->setError($this->_logToStr($log));
        } else {
            $log = $this->_addSeparatorToLog($log);
            $log[] = "Migrations tested in sandbox :" . $this->_logToStr($sandboxLog);
            $log = $this->_addSeparatorToLog($log);
        }

        /**
         * Load current environment state as version
         */
        if ($task->isUpdateFromCurrentRepoState()) {
            $task = $this->_loadCurrentRepoStateAsVersion(
                $task, $env, $log
            );

            if (!$task) return $task->setError($this->_logToStr($log));
        }

        $log[] = "Run Pre Process script:";
        $log[] = $env->runPreProcessScript();

        $this->_forceDownMigrationsIfExists($task, $env, $log);

        /**
         * update repo to new revision
         */
        if ($up = $repo->update($task['targetRevision'])) {
            $log[] = "Files updated to revision " . $task['targetRevision'];
            $log[] = $up;
        } else {
            /**
             * rollback - update repo to old revision
             * and up all migrations
             */
            $log[] = "Error occurred on updating to target revision " . $task['targetRevision'];
            $log[] = "Start Rollback, Update files to StartRevision:";
            $log[] = $repo->update($task['startRevision']);

            $log[] = "Run ProcessScript:";
            $log[] = $env->runProcessScript(); //after any repo update we must run process script

            $log[] = "Up migrations:";
            $log[] = $env->upMigrations();

            $log[] = "Run PostProcessScript:";
            $log[] = $env->runPostProcessScript();

            return $task->setError($this->_logToStr($log));
        }

        $log[] = "Run Process Script";
        $log[] = $env->runProcessScript();

        $log[] = "Run conflict resolving in migrations";
        $log[] = $env->resolveConflicts();


        try {
            $log[] = "Run migrations UP";
            $log[] = $env->upMigrations();
        } catch (Core_Exception $e) { //try to revert update

            return $task->setError(
                $this->_logToStr(
                    $this->_rollbackOnMigrationFail($env, $task, $repo, $log)
                )
            );
        }

        $log[] = "Run Post Process Script";
        $log[] = $env->runPostProcessScript();

        $log[] = "Update time :" . (microtime(true) - $startTime);

        if ($env->isJenkinsJobAttached()) {
            $this->_runJenkinsJob($env, $log);
        }

        $task->setProcessed($this->_logToStr($log));

        return true;
    }

    /**
     * @param Deploy_Model_Environment $env
     * @param Deploy_Model_UpdateTask $task
     * @param Deploy_Model_Repository $repo
     * @param array $log
     *
     * @return array
     */
    private function _rollbackOnMigrationFail($env, $task, $repo, $log)
    {
        $log[] = "Error occurred on migrations UP";
        $log[] = "Start Rollback, force down migrations";

        $rollbackTask = new Deploy_Model_UpdateTask();
        $rollbackTask->setFromArray(
            array('targetRevision' => $task['startRevision'])
        );

        $this->_forceDownMigrationsIfExists($rollbackTask, $env, $log);

        $log[] = "Update files to StartRevision:";
        $log[] = $repo->update($task['startRevision']);

        $log[] = "Run ProcessScript:";
        $log[] = $env->runProcessScript(); //after any repo update we must run process script

        $log[] = "Up migrations:";
        $log[] = $env->upMigrations();

        $log[] = "Run PostProcessScript:";
        $log[] = $env->runPostProcessScript();
        $log[] = "Rollback finished!";

        return $log;
    }

    /**
     * @param Deploy_Model_Environment $env
     * @param array                    $log
     *
     * @return bool
     */
    private function _runJenkinsJob(Deploy_Model_Environment $env, array &$log)
    {
        $jenkins = new Jenkins_JenkinsAPI(
            Options_Model_Options_Manager::get('basePath', 'Jenkins'),
            Options_Model_Options_Manager::get('user', 'Jenkins'),
            Options_Model_Options_Manager::get('token', 'Jenkins')
        );

        if (!$jenkins->isAvailable()) {
            $log[] = "Jenkins not available from this server";
            return false;
        }

        $config = $jenkins->getJobConfig($env->jenkinsJobName);

        if (!$config) {
            $log[] = "Can't load Jenkins Job config";
            return false;
        }

        $projectConfig = new SimpleXMLElement($config);
        $updatedConfig = '';

        foreach ($projectConfig->scm as $scm) {
            if ($scm['class'] == 'hudson.plugins.mercurial.MercurialSCM') {
                $scm->branch = $env->getRepo()->getCurrentBranchName();
                $updatedConfig = $projectConfig->asXML();
            }
        }

        if (!$updatedConfig) {
            $log[] = "Can't update Jenkins Job config.";
            return false;
        }

        try {
            $jenkins->setJobConfig($env->jenkinsJobName, $updatedConfig);
        } catch (RuntimeException $ex) {
            $log[] = "Can't update Jenkins Job config : " . $ex->getMessage();
            return false;
        }

        try {
            $jenkins->launchJob($env->jenkinsJobName);
        } catch (RuntimeException $ex) {
            $log[] = "Can't run Jenkins Job : " . $ex->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Sandbox emulator
     * @param Deploy_Model_UpdateTask $task
     * @param Deploy_Model_Environment $sourceEnv
     * @param array $log
     * @return bool
     */
    protected function _testUpdateInSandBox(Deploy_Model_UpdateTask $task,
                                            Deploy_Model_Environment $sourceEnv, array & $log)
    {
        $env = $this->_getSandBoxEnvironment();
        $repo = $env->getRepo();

        try {

            if (!$this->_prepareSandBox($env, $sourceEnv, $log)) {
                $log[] = "Can't prepare sandbox!";
                return false;
            }

            $sandboxParameters = $env->validateAllParameters();

            if (!$sandboxParameters['isEnvironmentReady']) {
                $log[] = "Sandbox environment not ready :";
                $log[] = print_r($sandboxParameters, true);
                return false;
            }

            if (!$this->_prepareRepo($task, $repo, $log)) {
                return false;
            }

            $this->_forceDownMigrationsIfExists($task, $env, $log);

            if (!$repo->update($task['targetRevision'])) {
                return false;
            }

            $env->resolveConflicts(); $log[] = "Run conflict resolving in migrations";

            $env->upMigrations(); $log[] = "Run migrations UP";
        } catch (Core_Exception $e) {
            $log[] = $e->getMessage();

            return false;
        }

        $result = ($env->isExistUnresolvableConflicts(false) == false);

        $log[] = "Finally clean db";
        $log[] = $this->_cleanSandboxDB($env->path);

        return $result;
    }

    /**
     * @return Deploy_Model_Environment
     */
    private function _getSandBoxEnvironment()
    {
        return $this->getDbTable()->createRow(
            array(
                'name' => 'sandbox',
                'path' => Options_Model_Options_Manager::get('path', 'SandBox'),
                'processScript' => Options_Model_Options_Manager::get('path', 'SandBox')
                    . DIRECTORY_SEPARATOR . '/update.sh'
            )
        );
    }

    /**
     * ForceDown migration - migration, which exists in current
     * revision, but not exists in target revision
     *
     * @param Deploy_Model_UpdateTask $task
     * @param Deploy_Model_Environment $env
     * @param array $log
     */
    private function _forceDownMigrationsIfExists(Deploy_Model_UpdateTask $task,
                                                  Deploy_Model_Environment $env, array &$log)
    {
        $migrationsForForceDown = $env->getMigrationsForForceDown($task['targetRevision']);

        if (is_array($migrationsForForceDown) && count($migrationsForForceDown) > 0) {

            $log[] = "Found migrations for force down : " . implode(PHP_EOL, $migrationsForForceDown);

            $log[] = $env->forceDownMigrations(
                $migrationsForForceDown
            );
        }
    }

    /**
     * @param Deploy_Model_Environment $env
     * @param Deploy_Model_Environment $sourceEnv
     * @param array                    $log
     *
     * @return bool
     */
    private function _prepareSandBox(Deploy_Model_Environment $env,
                                     Deploy_Model_Environment $sourceEnv, array &$log)
    {
        /*
         *  Update sandbox to current revision
         */
        $repo = $env->getRepo();

        $log[] = "Pull updates in sandbox";

        /**
         * pull changes to curr repo
         */
        if ($pullResult = $repo->pull()) {
            $log[] = $pullResult;
        } else {
            $log[] = "Error on pull to environment repo";
            return false;
        }

        $log[] = "Update sandbox";

        if ($up = $repo->update(
            $sourceEnv->getRepo()->getCurrentVersion()
        )) {
            $log[] = $up;
        } else {
            $log[] = "Error on update to environment repo";
            return false;
        }

        $env->runProcessScript();

        /*
         *  clean sandbox db
         */
        $log[] = $this->_cleanSandboxDB($env->path);

        $log[] = "Up migrations in sandbox";
        $log[] = $env->upMigrations();
    }

    /**
     * @param $sandboxPath
     *
     * @return string
     */
    private function _cleanSandboxDB($sandboxPath)
    {
        chdir($sandboxPath);

        $log = array();

        $log[] = "Clean sandbox db";
        $log[] = @shell_exec(
            "php -f cron.php cron/console/clean-db sandbox"
        );

        return implode(PHP_EOL, $log);
    }

    /**
     * @param Deploy_Model_UpdateTask $task
     * @param Deploy_Model_Repository $repo
     * @param array $log
     * @return bool
     */
    private function _prepareRepo(Deploy_Model_UpdateTask $task,
                                  Deploy_Model_Repository $repo, array &$log)
    {
        /**
         * pull changes to curr repo
         */
        if ($pullResult = $repo->pull()) {
            $log[] = $pullResult;
        } else {
            $log[] = "Error on pull to environment repo";
            return false;
        }

        /**
         * check is target revision exists
         */
        if ($repo->isRevisionExists($task['targetRevision'])) {
            $log[] =  "Revision " . $task['targetRevision'] . " found ";
        } else {
            $log[] = "Revision " . $task['targetRevision'] . " NOT found ";
            return false;
        }

        return true;
    }

    /**
     * @param Deploy_Model_UpdateTask $task
     * @param Deploy_Model_Environment $env
     * @param array $log
     * @return Deploy_Model_UpdateTask|null
     */
    private function _loadCurrentRepoStateAsVersion(Deploy_Model_UpdateTask $task,
                                                    Deploy_Model_Environment $env, array &$log)
    {
        $repo = $env->getRepo();
        $currRevision = $repo->getCurrentVersion();

        // clean changes by update to current revision with --clean
        $repo->update($currRevision);

        if ($currentVersionID = $env->loadCurrentStateAsVersion()) {
            $task->saveStartVersion($currentVersionID);
            $task['startRevision'] =  $currRevision;
            $task['startVersion'] = $currentVersionID;
            $log[] = "Current state loaded as version";

            return $task;
        } else {
            $log[] = "Error occurred on loading current state";

            return null;
        }
    }

    private function _addSeparatorToLog($log, $primitive = '=')
    {
        $log[] = str_repeat($primitive, 20);
        return $log;
    }

    /**
     * @param array $log
     *
     * @return string
     */
    private function _logToStr(array $log)
    {
        return implode(PHP_EOL, $log);
    }
}
