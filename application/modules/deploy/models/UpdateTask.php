<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * UpdateTask.php
 * Date: 09.11.12
 */
class Deploy_Model_UpdateTask extends Core_Db_Table_Row_Abstract
{

    const STATE_IN_QUEUE = 'inQueue';
    const STATE_PROCESSED = 'processed';
    const STATE_ERROR = 'error';
    const STATE_LOCKED = 'locked';

    /**
     * @param $log
     *
     * @return mixed
     */
    public function setError($log)
    {
        $task = $this;

        if ($this->_readOnly) {
            $task = $this->getTable()->getById($this->_data['id']);
        }

        $task->state = self::STATE_ERROR;
        $task->log = $log;

        return $task->save();
    }

    /**
     * @param $log
     *
     * @return mixed
     */
    public function setProcessed($log)
    {
        $task = $this;

        if ($this->_readOnly) {
            $task = $this->getTable()->getById($this->_data['id']);
        }

        $task->state = self::STATE_PROCESSED;
        $task->log = $log;

        return $task->save();
    }

    /**
     * @param $versionID
     *
     * @return mixed
     */
    public function saveStartVersion($versionID)
    {
        $task = $this;

        if ($this->_readOnly) {
            $task = $this->getTable()->getById($this->_data['id']);
        }

        $task->startVersion = $versionID;

        return $task->save();
    }

    /**
     * @return mixed
     */
    public function lock()
    {
        $task = $this;

        if ($this->_readOnly) {
            $task = $this->getTable()->getById($this->_data['id']);
        }

        $task->state = self::STATE_LOCKED;

        return $task->save();
    }

    /**
     * @return bool
     */
    public function isUpdateFromCurrentRepoState()
    {
        return $this->_data['startVersion'] == null;
    }

}
