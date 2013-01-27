<?php

class Migration_20121221_121637_34 extends Core_Migration_Abstract
{

    public function up()
    {
        $this->query(
            "ALTER TABLE `users`
	            CHANGE COLUMN `role` `role`
	              ENUM('guest','user','admin','developer', 'verifier')
	              NOT NULL DEFAULT 'guest' COMMENT 'Defined in Users_Model_User' AFTER `avatar`;"
        );
    }

    public function down()
    {
        $this->query(
            "ALTER TABLE `users`
	            CHANGE COLUMN `role` `role`
	              ENUM('guest','user','admin')
	              NOT NULL DEFAULT 'guest' COMMENT 'Defined in Users_Model_User' AFTER `avatar`;"
        );
    }

}

