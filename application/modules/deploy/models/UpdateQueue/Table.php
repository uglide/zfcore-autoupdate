<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Table.php
 * Date: 09.11.12
 */
class Deploy_Model_UpdateQueue_Table extends Core_Db_Table_Abstract
{
    /** Table name */
    protected $_name = 'update_queue';

    /** Primary Key */
    protected $_primary = 'id';

    /** Row Class */
    protected $_rowClass = 'Deploy_Model_UpdateTask';

    const DEFAULT_LIMIT = 20;

    /**
     * @param $envID
     * @return null|Zend_Db_Table_Row_Abstract
     * @throws InvalidArgumentException
     */
    public function getLastNotProcessedTask($envID)
    {
        if (!(int)$envID > 0) {
            throw new InvalidArgumentException('Provide Env ID');
        }

        return $this->fetchRow(
            $this->select()
                ->setIntegrityCheck(false)
                ->from(array('t' => $this->info('name')))
                ->joinLeft(
                    array('vt' => 'version'),
                    'vt.id = targetVersion',
                    array('targetRevision' => 'revision')
                )
                ->joinLeft(
                    array('vs' => 'version'),
                    'vs.id = startVersion',
                    array('startRevision' => 'revision')
                )
                ->where('t.environmentID = ?', $envID)
                ->where(
                    'state IN (?)',
                    array(
                        Deploy_Model_UpdateTask::STATE_IN_QUEUE,
                        Deploy_Model_UpdateTask::STATE_LOCKED
                    )
                )
                ->order('date DESC')
                ->limit(1)
        );
    }

    /**
     * @param int $limit
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getNotProcessedTasks($limit = 3)
    {
        return $this->fetchAll(
            $this->select()
                ->setIntegrityCheck(false)
                ->from(array('t' => $this->info('name')))
                ->joinLeft(
                    array('vt' => 'version'),
                    'vt.id = targetVersion',
                    array('targetRevision' => 'revision')
                )
                ->joinLeft(
                    array('vs' => 'version'),
                    'vs.id = startVersion',
                    array('startRevision' => 'revision')
                )
                ->where('state = ?', Deploy_Model_UpdateTask::STATE_IN_QUEUE)
                ->order('date DESC')
                ->limit($limit)
        );
    }

    /**
     * @return bool
     */
    public function isLockedTasksExists()
    {
        $notProcessedTask = $this->fetchRow(
            $this->select()
                ->where('state = ?', Deploy_Model_UpdateTask::STATE_LOCKED)
                ->order('date DESC')
                ->limit(1)
        );

        return $notProcessedTask != null;
    }

    /**
     * @param $envID
     * @param $userID
     * @param $targetVersion
     * @param null $startVersion
     * @return mixed
     */
    public function addTask($envID, $userID,  $targetVersion, $startVersion = null)
    {
        return $this->insert(
            array(
                'environmentID' => $envID,
                'userID' => $userID,
                'targetVersion' => $targetVersion,
                'startVersion' => $startVersion
            )
        );
    }

    /**
     * @param $envID
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getHistoryForEnvironment($envID)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('t' => $this->info('name')))
            ->joinLeft(
                array('u' => 'users'),
                't.userID = u.id',
                array(
                   'user' => new Zend_Db_Expr(
                       " CONCAT(u.firstname, ' ', u.lastname, ' (', u.email, ')') "
                   ),
                )
            )
            ->joinLeft(
                array('vs' => 'version'),
                't.startVersion = vs.id',
                 array('startRevision' => 'revision')
            )
            ->joinLeft(
                array('vv' => Deploy_Model_Version_Manager::VERIFY_TABLE),
                't.targetVersion = vv.versionID',
                array('verifiedBy')
            )
            ->joinLeft(
                array('u2' => 'users'),
                'vv.verifiedBy = u2.id',
                array(
                     'verifiedByUser' => new Zend_Db_Expr(
                         " CONCAT(u2.firstname, ' ', u2.lastname, ' (', u2.email, ')') "
                     ),
                )
            )
            ->joinLeft(
                array('vt' => 'version'),
                't.targetVersion = vt.id',
                array('targetRevision' => 'revision')
            )->where('t.state <> ?', Deploy_Model_UpdateTask::STATE_IN_QUEUE)
             ->where('t.environmentID = ? ', $envID)
            ->limit(self::DEFAULT_LIMIT)
            ->order('t.date DESC');

        return $this->fetchAll($query);
    }

    /**
     * @param $environmentID
     *
     * @return array|null
     */
    public function getStats($environmentID)
    {
        $statsRaw = $this->fetchAll(
            $this->select()
                ->setIntegrityCheck(false)
                ->from(
                    array('s' => $this->info('name')),
                    array(
                        'day' => 'DATE(`date`)',
                        'updatesCount' =>
                            ' ( SELECT COUNT(*)
                                    FROM update_queue
                                    WHERE
                                        DATE(date) = day
                                        AND environmentID = s.environmentID
                               ) '
                    )
            )
            ->where('DATE(date) >= DATE_SUB(NOW(), INTERVAL 2 WEEK)')
            ->where('environmentID = ?', $environmentID)
            ->order('day ASC')
            ->group('day')
        );

        if (!$statsRaw) {
            return null;
        }

        $formattedStats = array(
            array(
                'Day', 'Updates'
            )
        );

        foreach ($statsRaw as $stat) {
            $formattedStats[] = array($stat['day'], (int)$stat['updatesCount']);
        }

        return $formattedStats;

    }

}
