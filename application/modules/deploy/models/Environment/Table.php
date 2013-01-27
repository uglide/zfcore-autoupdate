<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>
 * Date: 27.10.12
 * Time: 15:25
 */

class Deploy_Model_Environment_Table extends Core_Db_Table_Abstract
{
    /** Table name */
    protected $_name = 'environments';

    /** Primary Key */
    protected $_primary = 'id';

    /** Row Class */
    protected $_rowClass = 'Deploy_Model_Environment';


    public function getAll()
    {
        $query = $this->select()
            ->from(
            array('e' => $this->info('name')),
            array(
                '*',
                '(SELECT `revision` FROM version WHERE environmentID = e.id ORDER BY id DESC LIMIT 1) as
                currentVersion',
                '(SELECT `state` FROM update_queue WHERE environmentID = e.id ORDER BY date DESC LIMIT 1) as
                lastUpdateState'
            )
        );

        return $this->fetchAll(
            $query
        );
    }


    public function getAllGroupedByType()
    {
        $env = $this->getAll();

        if (!count($env)) {
            return array();
        }

        $envGrouped = array();

        foreach ($env as $e) {
            if (!array_key_exists($e->type, $envGrouped)) {
                $envGrouped[$e->type] = array($e);
            } else {
                $envGrouped[$e->type][] = $e;
            }
        }

        return $envGrouped;
    }

}
