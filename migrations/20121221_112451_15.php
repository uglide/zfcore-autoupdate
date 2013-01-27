<?php

class Migration_20121221_112451_15 extends Core_Migration_Abstract
{

    public function up()
    {
        $this->query(
            "CREATE TABLE `verified_versions` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `versionID` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                `verifiedBy` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                CONSTRAINT `FK_versionID` FOREIGN KEY (`versionID`) REFERENCES `version` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
                CONSTRAINT `FK_verifiedBy` FOREIGN KEY (`verifiedBy`) REFERENCES `users` (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;"
        );

        $this->query(
            "ALTER TABLE `environments`
	          ADD COLUMN `type` ENUM('test','stage','live') NOT NULL DEFAULT 'test' AFTER `jenkinsJobName`;"
        );
    }

    public function down()
    {
        $this->query(
            "DROP TABLE `verified_versions`;"
        );

        $this->query(
            "ALTER TABLE `environments`
                DROP COLUMN `type`"
        );
    }


}

