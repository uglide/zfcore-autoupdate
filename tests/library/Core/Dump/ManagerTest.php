<?php
/**
 * ManagerTest.php
 * Created by glide <u.glide@gmail.com>.
 * Date: 26.01.12
 */

class Core_Dump_ManagerTest extends ControllerTestCase
{
    protected $_manager = null;

    const DUMP_FILE_NAME = 'test.sql';

    const FIXTURE_MODULE = 'testmodule';

    const TABLE_NAME = 'test_table';

    /**
     * @return Core_Dump_Manager
     */
    protected function _getManager()
    {
        if (null === $this->_manager) {
            $this->_manager = new Core_Dump_Manager(
                array(
                    'projectDirectoryPath' => dirname(__FILE__) . '/_env/',
                    'modulesDirectoryPath' => dirname(__FILE__) . '/_env/application/modules/',
                    'migrationsDirectoryName' => 'dumps',
                )
            );
        }

        return $this->_manager;
    }


    public function testCreateSuccess()
    {
        $db = new Core_Migration_Adapter_Mysql(Zend_Db_Table::getDefaultAdapter());

        $db->query(Core_Db_Database::dropTable(self::TABLE_NAME));
        $db->createTable(self::TABLE_NAME);
        $db->createColumn(self::TABLE_NAME, 'col1', Core_Migration_Abstract::TYPE_INT);
        $db->createColumn(self::TABLE_NAME, 'col2', Core_Migration_Abstract::TYPE_VARCHAR, 50);

        $db->query(Core_Db_Database::dropTable('test_black_table1'));
        $db->createTable('test_black_table1');

        $db->query(Core_Db_Database::dropTable('test_black_table2'));
        $db->createTable('test_black_table2');


        $dumpName = $this->_getManager()->create(
            self::FIXTURE_MODULE, self::DUMP_FILE_NAME, self::TABLE_NAME, 'test_black_table1,test_black_table2'
        );

        $compareTo = Core_Db_Database::dropTable(self::TABLE_NAME) . ';' . PHP_EOL
            . Core_Db_Database::createTable(self::TABLE_NAME). ';' . PHP_EOL;

        $this->assertEquals(self::DUMP_FILE_NAME, $dumpName);

        $dumpFullPath = $this->_getManager()->getDumpsDirectoryPath(self::FIXTURE_MODULE)
            . DIRECTORY_SEPARATOR . $dumpName;

        if (file_exists($dumpFullPath)) {

            $dump = file_get_contents($dumpFullPath);
            $this->assertEquals($compareTo, $dump);

        } else {
            $this->fail('Dump file not exist!');
        }

        $db->dropTable('test_table');
        $db->dropTable('test_black_table1');
        $db->dropTable('test_black_table2');
    }

    /**
     * @depends testCreateSuccess
     */
    public function testImportSuccess()
    {
        $dumpFullPath = $this->_getManager()->getDumpsDirectoryPath(self::FIXTURE_MODULE)
            . DIRECTORY_SEPARATOR . self::DUMP_FILE_NAME;

        if (file_exists($dumpFullPath)) {

            $dump = file_get_contents($dumpFullPath);

            $this->_getManager()->import(self::DUMP_FILE_NAME, self::FIXTURE_MODULE);

            $result = Zend_Db_Table_Abstract::getDefaultAdapter()
                ->query("SHOW TABLES LIKE '".self::TABLE_NAME."';");

            $this->assertEquals(1, $result->rowCount());

        } else {
            $this->fail('Dump file not exist!');
        }
    }


    public function testImportFail()
    {
        try {
            $this->_getManager()->import('', null);
        } catch (Exception $expected) {
            $this->assertTrue(true);
            return;
        }

        $this->fail("No exception!");
    }


}