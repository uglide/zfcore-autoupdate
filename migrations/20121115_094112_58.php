<?php

class Migration_20121115_094112_58 extends Core_Migration_Abstract
{

    public function up()
    {
        $this->query(
            "ALTER TABLE `update_queue`
	          CHANGE COLUMN `state` `state`
	            ENUM('inQueue', 'locked' ,'processed','error') NOT NULL DEFAULT 'inQueue' AFTER `environmentID`;"
        );
    }

    public function down()
    {
        $this->query(
            "ALTER TABLE `update_queue`
	          CHANGE COLUMN `state` `state`
	            ENUM('inQueue', 'processed','error') NOT NULL DEFAULT 'inQueue' AFTER `environmentID`;"
        );
    }


}

