<?php
/**
 * Zero migration
 * Required for correct back to start
 */
class Migration_00000000_000000_00 extends Core_Migration_Abstract
{
    public function up()
    {
        $this->query(
            "SET FOREIGN_KEY_CHECKS=0;"
        );

        // users table
        $this->query("
            CREATE TABLE `users` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `login` varchar(255) NOT NULL,
              `email` varchar(255) NOT NULL,
              `password` varchar(64) NOT NULL,
              `salt` varchar(32) NOT NULL,
              `firstname` varchar(255) DEFAULT NULL,
              `lastname` varchar(255) DEFAULT NULL,
              `avatar` varchar(512) DEFAULT NULL COMMENT 'Path to image',
              `role` enum('guest','user','admin') NOT NULL DEFAULT 'guest' COMMENT 'Defined in Users_Model_User',
              `status` enum('active','blocked','registered','removed') NOT NULL DEFAULT 'registered' COMMENT 'Defined in Users_Model_User',
              `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `logined` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `ip` int(11) DEFAULT NULL,
              `count` int(11) NOT NULL DEFAULT '1' COMMENT 'Login counter',
              `hashCode` varchar(32) DEFAULT NULL,
              `inform` enum('true','false') NOT NULL DEFAULT 'false',
              `facebookId` varchar(250) DEFAULT NULL,
              `twitterId` varchar(250) DEFAULT NULL,
              `googleId` varchar(250) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_login` (`login`),
              UNIQUE KEY `unique_email` (`email`),
              UNIQUE KEY `activate` (`hashCode`)
            ) ENGINE=InnoDB AUTO_INCREMENT=10066 DEFAULT CHARSET=utf8
        ");

        $this->query(
            "INSERT INTO `users` VALUES ('10066', 'admin', 'u.glide@gmail.com', '6feb80bdfed3d113bdd84c3381b6130d', '41419ca499ed7e57804a6d78355c1c33', 'Admin', 'Admin', null, 'admin', 'active', '2012-10-27 15:23:54', '2012-11-13 15:47:43', '2012-11-13 15:47:43', '168427811', '6', '3c95abc5a6758ef6b295ddee86595c83', 'false', null, null, null);"
        );

        $this->query("
            CREATE TABLE `mail_templates` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `description` varchar(512) DEFAULT NULL,
              `subject` varchar(255) DEFAULT NULL,
              `bodyHtml` text NOT NULL,
              `bodyText` text NOT NULL,
              `alias` varchar(255) NOT NULL,
              `fromEmail` varchar(255) DEFAULT NULL,
              `fromName` varchar(255) DEFAULT NULL,
              `signature` enum('true','false') NOT NULL DEFAULT 'true',
              PRIMARY KEY (`id`),
              UNIQUE KEY `mail_templates_unique` (`alias`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $this->insert('mail_templates', array(
            'alias'   =>  'registration',
            'subject' =>  'Registration on %host%',
            'description' => 'User registration letter',
            'bodyHtml'    =>  'Please, confirm your registration<br/><br/>'.
                'Click the folowing link:<br/>'.
                '<a href="http://%host%/users/register/confirm-registration/hash/%hash%">http://%host%/users/register/confirm-registration/hash/%hash%</a>'.
                '<br />'.
                'With best regards,<br />'.
                '<a href="http://%host%/>%host% team</a>',
            'bodyText' =>  'Please confirm your registration\n\n'.
                'Open the folowing link in your browser: \n'.
                'http://%host%/users/register/confirm-registration/hash/%hash%'.
                "\n\n\n".
                "With best regards,\n".
                "%host% team",
            'signature' => 'true'
        ));
        $this->insert('mail_templates', array(
            'alias'   =>  'forgetPassword',
            'subject' =>  'Forget password on %host%',
            'description' => 'User forget password letter',
            'bodyHtml'    =>  'You\'re ask to reset your password.<br/><br/>'.
                'Please confirm that you wish to reset it clicking on the url:<br />'.
                '<a href="http://%host%/users/login/recover-password/hash/%hash%/">http://%host%/users/login/recover-password/hash/%hash%/</a><br/><br/>'.
                'If this message was created due to mistake, you can cancel password reset via next link:<br />'.
                '<a href="http://%host%/users/login/cancel-password-recovery/hash/%hash%/">
http://%host%/users/login/cancel-password-recovery/hash/%hash%/</a>'.
                '<br />'.
                'With best regards,<br />'.
                '<a href="http://%host%/>%host% team</a>',
            'bodyText' =>  'You\'re ask to reset your password.\n\n'.
                'Please confirm that you wish to reset it clicking on the url:\n'.
                'http://%host%/users/login/recover-password/hash/%hash%/\n\n'.
                'If this message was created due to mistake, you can cancel password reset via next link:\n'.
                'http://%host%/users/login/cancel-password-recovery/hash/%hash%/'.
                "\n\n\n".
                "With best regards,\n".
                "%host% team",
            'signature' => 'true'
        ));


        $this->insert('mail_templates', array(
            'alias'   =>  'newPassword',
            'subject' =>  'New password for %host%',
            'description' => '',
            'bodyHtml'    =>  'You\'re ask to reset your password.<br/><br/>'.
                'Your new password is:<br />'.
                '<b>%password%</b>'.
                '<br />'.
                'With best regards,<br />'.
                '<a href="http://%host%/">%host% team</a>',
            'bodyText' =>  "You're ask to reset your password.\n\n".
                "Your new password is:\n".
                "%password%".
                "\n\n\n".
                "With best regards,\n".
                "%host% team",
            'signature' => 'true'
        ));


        $this->insert('mail_templates', array(
            'alias'   =>  'reply',
            'subject' =>  'Thank you for your letter',
            'bodyHtml'    =>  'Thank you for your letter!'.
                '<br />'.
                'With best regards,<br />'.
                '<a href="http://%host%/">%host% team</a>',
            'bodyText' =>  "Thank you for your letter!".
                "\n\n\n".
                "With best regards,\n".
                "%host% team",
            'signature' => 'true'
        ));

        $this->query(
            "CREATE TABLE `environments` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(200) NOT NULL,
              `path` varchar(500) NOT NULL,
              `preProcessScript` varchar(500) NOT NULL,
              `processScript` varchar(500) NOT NULL,
              `postProcessScript` varchar(500) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;"
        );

        $this->query(
            "CREATE TABLE `version` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `revision` varchar(100) NOT NULL,
              `environmentID` bigint(20) unsigned NOT NULL,
              `loadedMigrations` text NOT NULL,
              `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `FK_environment` (`environmentID`),
              CONSTRAINT `FK_environment` FOREIGN KEY (`environmentID`) REFERENCES `environments` (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;"
        );

        $this->query(
            "CREATE TABLE `update_queue` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `startVersion` bigint(20) unsigned DEFAULT NULL,
              `targetVersion` bigint(20) unsigned NOT NULL,
              `userID` bigint(20) unsigned NOT NULL,
              `environmentID` bigint(20) unsigned NOT NULL,
              `state` enum('inQueue','processed','error') NOT NULL DEFAULT 'inQueue',
              `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `log` text,
              `error` text,
              PRIMARY KEY (`id`),
              KEY `FK_user_id` (`userID`),
              KEY `FK_startVersion` (`startVersion`),
              KEY `FK_targetVersion` (`targetVersion`),
              KEY `FK_env` (`environmentID`),
              CONSTRAINT `FK_env` FOREIGN KEY (`environmentID`) REFERENCES `environments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `FK_startVersion` FOREIGN KEY (`startVersion`) REFERENCES `version` (`id`),
              CONSTRAINT `FK_targetVersion` FOREIGN KEY (`targetVersion`) REFERENCES `version` (`id`),
              CONSTRAINT `FK_user_id` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;"
        );

        $this->query(
            "CREATE TABLE `options` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `value` longtext NOT NULL,
              `type` enum('int','float','string','array','object') NOT NULL DEFAULT 'string',
              `namespace` varchar(64) NOT NULL DEFAULT 'default',
              PRIMARY KEY (`id`,`name`,`namespace`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;"
        );

        $this->query(
            "INSERT INTO `options` VALUES ('1', 'path', '/home/glide/src/sandbox', 'string', 'SandBox');"
        );

        $this->query(
            "SET FOREIGN_KEY_CHECKS=1;"
        );

    }

    public function down()
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $db->query("SET FOREIGN_KEY_CHECKS = 0;");

        //clean tables
        $allTables = $db->listTables();

        foreach ($allTables as $tblName) {
            $db->query(
                "DROP TABLE IF EXISTS " . $db->quoteIdentifier($tblName)
            );
        }

        //clean procedures
        $procedures = $db->query(
            "SHOW PROCEDURE STATUS WHERE `Db`=?", array($db->getConfig()['dbname'])
        );

        $procedures = $procedures->fetchAll();

        foreach ($procedures as $procedure) {
            $db->query(
                "DROP PROCEDURE IF EXISTS " . $db->quoteIdentifier($procedure['Name'])
            );
        }
    }
}

