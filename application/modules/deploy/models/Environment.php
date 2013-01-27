<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>
 * Date: 27.10.12
 * Time: 15:25
 */

class Deploy_Model_Environment extends Core_Db_Table_Row_Abstract
{
    const MIGRATION_STATUS_CONFLICT = 'C';
    const MIGRATION_STATUS_LOADED_AND_NOT_FOUND = 'LN';

    const MIGRATION_LIST_STATES = 1;
    const MIGRATION_LIST_NAMES = 2;

    const TYPE_TEST = 'test';
    const TYPE_STAGE = 'stage';
    const TYPE_LIVE = 'live';

    const STATUS_READY = 'valid';
    const STATUS_ERROR = 'error';
    /**
     * @var Deploy_Model_Repository
     */
    private $_repo;

    private $_version = null;

    public function getVersion($forceCache = true)
    {
        if ($this->_version != null && !$forceCache) {
            return $this->_version;
        }

        $versionTable = new Deploy_Model_Version_Table();
        return $versionTable->fetchRow(
            $versionTable->select()
                ->from(
                    array('v' => $versionTable->info('name'))
                )
                ->setIntegrityCheck(false)
                ->joinLeft(
                    array('up' => 'update_queue'),
                    'up.targetVersion = v.id',
                    array()
                )
                ->limit(1)
                ->order('v.id DESC')
                ->where('v.environmentID = ?', $this->_data['id'])
                ->where('up.state = \'processed\'')
        );
    }

    public function checkPath()
    {
        return (is_dir($this->_data['path']));
    }

    public function checkPreProcessScript()
    {
        return empty($this->_data['preProcessScript']) || file_exists($this->_data['preProcessScript']);
    }

    public function checkProcessScript()
    {
        return empty($this->_data['preProcessScript']) || file_exists($this->_data['processScript']);
    }

    public function checkPostProcessScript()
    {
        return empty($this->_data['preProcessScript']) || file_exists($this->_data['postProcessScript']);
    }

    public function getRepo()
    {
        if (null == $this->_repo) {
            $this->_repo = new Deploy_Model_Repository($this->_data['path']);
        }

        return $this->_repo;
    }

    public function getRealVersionInPath()
    {
        if ($this->checkPath()) {
            $repo = $this->getRepo();

            return $repo->getCurrentVersion();
        }

        return null;
    }

    /**
     * @return string
     */
    public function resolveConflicts()
    {
        $result = shell_exec($this->_getMigrationsPrefix() . 'resolve migration');

        return $result;
    }

    public function upMigrations()
    {
        $output = array();
        $returnCode = 0;

        exec($this->_getMigrationsPrefix() . 'up migration', $output, $returnCode);

        $output = implode(PHP_EOL, $output);

        if ($returnCode != 0 || strpos($output, 'An Error Has Occurred') !== false) {
            throw new Core_Exception(
                'Error occurred on migration up! :'
                . PHP_EOL . $output
            );
        }

        return $output;
    }

    public function runPreProcessScript()
    {
        if (!$this->checkPreProcessScript()) {
            return "PreProcess script not found for this environment";
        }

        chdir($this->_data['path']);
        return @shell_exec('sh ' . $this->_data['preProcessScript']);
    }

    public function runProcessScript()
    {
        if (!$this->checkProcessScript()) {
            return "Process script not found for this environment";
        }

        chdir($this->_data['path']);
        return @shell_exec('sh ' . $this->_data['processScript']);
    }

    public function runPostProcessScript()
    {
        if (!$this->checkPostProcessScript()) {
            return "PostProcess script not found for this environment";
        }

        chdir($this->_data['path']);
        return @shell_exec('sh ' . $this->_data['postProcessScript']);
    }

    /**
     * @return string
     */
    public function getMigrationsRawList()
    {
        $output = 'Get migrations list: ' . PHP_EOL;

        $output .= trim((string)@shell_exec($this->_getMigrationsPrefix() . 'listing migration'));

        return $output;
    }

    public function forceDownMigrations(array $migrations)
    {
        return trim(
            (string)@shell_exec(
                $this->_getMigrationsPrefix() . 'force-down migration ' . implode(',', $migrations)
            )
        );
    }

    public function getMigrationsFromText($text, $namesOnly = true)
    {
        $result = array();

        preg_match_all("/\[(.)\]([0-9]{8}_[0-9]{6}_[0-9]{2}(_[0-9a-z_]*)*)/i", $text, $result);

        if ($namesOnly) return $result[self::MIGRATION_LIST_NAMES];
        return $result;
    }

    public function isExistUnresolvableConflicts($runConflictsResolvingBefore = true)
    {
        if ($runConflictsResolvingBefore) {
            $this->resolveConflicts();
        }

        $migrationsFlags = $this->getMigrationsFromText(
            $this->getMigrationsRawList(), false
        );

        return in_array(self::MIGRATION_STATUS_CONFLICT, $migrationsFlags[self::MIGRATION_LIST_STATES])
            || in_array(self::MIGRATION_STATUS_LOADED_AND_NOT_FOUND, $migrationsFlags[self::MIGRATION_LIST_STATES]);
    }

    public function getConflictedMigrations()
    {
        $migrations = $this->getMigrationsFromText(
            $this->getMigrationsRawList(), false
        );

        foreach ($migrations[self::MIGRATION_LIST_STATES] as $index => $migrationStatus) {
            if ($migrationStatus != self::MIGRATION_STATUS_CONFLICT) {
                unset($migrations[self::MIGRATION_LIST_STATES][$index]);
            }
        }

        return $migrations[self::MIGRATION_LIST_NAMES];
    }

    public function validateAllParameters()
    {
        $states = array(
            'validPath' => $this->checkPath(),
            'validPreProcessScript' => $this->checkPath()
                && $this->checkPreProcessScript(),
            'validProcessScript' => $this->checkPath()
                && $this->checkProcessScript(),
            'validPostProcessScript' => $this->checkPath()
                && $this->checkPostProcessScript(),
            'validRepo' => $this->checkPath()
                && $this->getRepo()->check(),
            'validMigrations' => $this->checkPath()
                && !$this->isExistUnresolvableConflicts(false),
            'validCentralRepo' => $this->checkPath()
                && $this->getRepo()->check()
                && $this->getRepo()->checkCentralRepo()
        );

        $states['isEnvironmentReady'] = $states['validPath']
            && $states['validPreProcessScript']
            && $states['validProcessScript']
            && $states['validPostProcessScript']
            && $states['validRepo']
            && $states['validMigrations']
            && $states['validCentralRepo'];

        return $states;
    }

    public function setValid()
    {
        $this->status = self::STATUS_READY;
        $this->save();
    }

    public function setInValid()
    {
        $this->status = self::STATUS_ERROR;
        $this->save();
    }

    /**
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function getVersionWaitingForUpdate()
    {
        $queueTable = new Deploy_Model_UpdateQueue_Table();

        return $queueTable->getLastNotProcessedTask($this->_data['id']);
    }

    /**
     * Load current revision of repository as
     * updater version
     * @return bool|mixed
     */
    public function loadCurrentStateAsVersion()
    {
        // try to resolve conflicts in migrations
        $this->resolveConflicts();

        /*
         * get migrations
         */
        $result = $this->getMigrationsFromText(
            $this->getMigrationsRawList(), false
        );

        if ($this->isExistUnresolvableConflicts()) {
            return false;
        } else {
            $versionTable = new Deploy_Model_Version_Table();
            $newVersion = $versionTable->createRow(
                array(
                    'environmentID' => $this->_data['id'],
                    'revision' => $this->_repo->getCurrentVersion(false),
                    'loadedMigrations' => serialize($result[2])
                )
            );

            return $newVersion->save();
        }
    }

    public function getMigrationsForForceDown($targetRevision)
    {
        $migrationsFromTargetRevision = $this->_getMigrationsFromFileList(
            $this->_repo->getRevisionFileList($targetRevision)
        );

        $migrationsInCurrentRevision = $this->getMigrationsFromText(
            $this->getMigrationsRawList()
        );

        return array_diff(
            $migrationsInCurrentRevision, $migrationsFromTargetRevision
        );
    }

    public function getNewMigrations($targetRevision)
    {
        $migrationsFromTargetRevision = $this->_getMigrationsFromFileList(
            $this->_repo->getRevisionFileList($targetRevision)
        );

        $migrationsInCurrentRevision = $this->getMigrationsFromText(
            $this->getMigrationsRawList()
        );

        return array_diff(
            $migrationsFromTargetRevision, $migrationsInCurrentRevision
        );
    }

    /**
     * @return bool
     */
    public function isCurrentVersionVerified()
    {
        $manager = new Deploy_Model_Version_Manager();
        $version = $this->getVersion(false);

        if (!$version) {
            return false;
        }

        return $manager->isVersionVerified($version->id);
    }

    public function isJenkinsJobAttached()
    {
        return $this->_data['runJenkinsJobAfterUpdate'] == 'yes';
    }

    public function shouldBeVerified()
    {
        return $this->_data['type'] === self::TYPE_STAGE
            || $this->_data['type'] === self::TYPE_TEST;
    }

    public function isStage()
    {
        return $this->_data['type'] === self::TYPE_STAGE;
    }

    public function isLive()
    {
        return $this->_data['type'] === self::TYPE_LIVE;
    }

    public function isReady()
    {
        return $this->_data['status'] === self::STATUS_READY;
    }

    private function _getMigrationsFromFileList($rawFileList)
    {
        $matches = array();

        preg_match_all(
            '/[^\/]migrations\/([0-9]{8}_[0-9]{6}_[0-9]{2}(_.*)*)\.php/i',
            $rawFileList, $matches
        );

        return $matches[1];
    }

    private function _getMigrationsPrefix()
    {
        chdir($this->_data['path'] . DIRECTORY_SEPARATOR . 'bin');
        return 'APPLICATION_ENV=' . $this->_data['name'] . ' sh zfc.sh ';
    }

}
