<?php

class Migration_20121116_123620_66 extends Core_Migration_Abstract
{

    public function up()
    {
        $this->query(
            "ALTER TABLE `environments`
	            ADD COLUMN `runJenkinsJobAfterUpdate` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `postProcessScript`,
	            ADD COLUMN `jenkinsJobName` VARCHAR(200) NULL AFTER `runJenkinsJobAfterUpdate`;"
        );
    }

    public function down()
    {
        $this->query(
            "ALTER TABLE `environments`
	            DROP COLUMN `runJenkinsJobAfterUpdate`,
	            DROP COLUMN `jenkinsJobName`;"
        );
    }


}

