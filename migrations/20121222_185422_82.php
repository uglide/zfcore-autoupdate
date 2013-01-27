<?php

class Migration_20121222_185422_82 extends Core_Migration_Abstract
{

    public function up()
    {
        $this->query(
            "ALTER TABLE `environments`
	          ADD COLUMN `status` ENUM('valid','error') NULL DEFAULT 'error' AFTER `type`;"
        );
    }

    public function down()
    {
        $this->query(
            "ALTER TABLE `environments`
	          DROP COLUMN `status`;"
        );
    }


}

