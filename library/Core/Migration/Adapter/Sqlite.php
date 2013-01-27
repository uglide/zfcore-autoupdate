<?php
/**
 * Copyright (c) 2012 by PHP Team of NIX Solutions Ltd
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Class Core_Migration_Adapter_Mysql
 *
 * @category Core
 * @package  Core_Migration
 * @subpackage Adapter
 *
 * @author   Alexey Novikov <oleksii.novikov@gmail.com>
 */
class Core_Migration_Adapter_Sqlite extends Core_Migration_Adapter_Abstract
{
    /**
     * Create table
     *
     * @param string $table
     * @return Core_Migration_Adapter_Abstract
     */
    public function createTable($table)
    {
        $this->query(
            'CREATE TABLE ' .
            $table .
            ' ( id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT )'
        );
        return $this;
    }

    /**
     * dropTable
     *
     * @param   string     $table  table name
     * @return  Core_Migration_Abstract
     */
    public function dropTable($table)
    {
        $this->query('DROP TABLE ' . $this->getDbAdapter()->quote($table));
        return $this;
    }

    /**
     * createColumn
     *
     * FIXME: requried quoted queries data
     *
     * @param   string   $table
     * @param   string   $column
     * @param   string   $datatype
     * @param   string   $length
     * @param   string   $default
     * @param   bool     $notnull
     * @param   bool     $primary
     * @return  bool
     */
    public function createColumn(
        $table,
        $column,
        $datatype,
        $length = null,
        $default = null,
        $notnull = false,
        $primary = false
    )
    {

        $fullField = '';
        $column = $this->getDbAdapter()->quoteIdentifier($column);
        // switch statement for $datatype
        switch ($datatype) {
            case Core_Migration_Abstract::TYPE_VARCHAR:
                $length = $length ? $length : 255;
                $fullField .= " VARCHAR($length)";
                break;
            case Core_Migration_Abstract::TYPE_FLOAT:
                $length = $length ? $length : '0,0';
                $fullField .= " FLOAT($length)";
                break;
            //TODO ENUM!!!
            case Core_Migration_Abstract::TYPE_ENUM:
                if (is_array($length)) {
                    $length = join(",", $length);
                }
                $fullField .= " ENUM '" . $length . "'";
                break;
            default:
                $fullField .= $datatype;
                break;
        }
        if (!is_null($default)) {
            // switch statement for $datatype
            switch ($datatype) {
                case (Core_Migration_Abstract::TYPE_TIMESTAMP) && ($default === 'CURRENT_TIMESTAMP'):
                    $fullField .= " DEFAULT CURRENT_TIMESTAMP";
                    break;
                default:
                    $fullField .= ' DEFAULT '
                        . $this->getDbAdapter()->quote($default);
                    break;
            }
        }

        if ($notnull) {
            $fullField .= " NOT NULL";
        } else {
            $fullField .= " NULL";
        }

        //if column is a primary key or NOT NULL & Default is not set or not constant
        //in this case we must recreate table with new column
        if ($primary || ($notnull && (is_null($default) || $default === 'CURRENT_TIMESTAMP'))) {
            $fields = $this->getDbAdapter()->describeTable($table);
            $allCols = array();
            $collNames = array();
            foreach ($fields as $field => $options) {
                $collNames[] = $options['COLUMN_NAME'];

                $allCols[$field] = $this->getDbAdapter()->quoteIdentifier($options['COLUMN_NAME'])
                    . ' ' . $options['DATA_TYPE'];

                if ($options['LENGTH']) {
                    $allCols[$field] .= '(' . $options['LENGTH'] . ')';
                }
                if (!$options['NULLABLE']) {
                    $allCols[$field] .= ' NOT NULL ';
                }
                if ($options['DEFAULT']) {
                    $allCols[$field] .= " DEFAULT "
                        . $this->getDbAdapter()->quote($options['DEFAULT']);
                }
            }

            //Full info about columns  exept  names
            $colls = join(',', $allCols);
            //Names of columns
            $collNames = $this->_quoteIdentifierArray($collNames);
            $table = $this->getDbAdapter()->quoteIdentifier($table);

            $newQuery = 'CREATE TEMPORARY TABLE t1_backup(' . $colls . ')';
            $this->query($newQuery);
            $newQuery = 'INSERT INTO t1_backup SELECT ' . $collNames
                . ' FROM ' . $table;
            $this->query($newQuery);
            $newQuery = 'DROP TABLE ' . $table;
            $this->query($newQuery);
            $newQuery = 'Create TABLE ' . $table .
                ' (' . $colls . ',' . $column . '
                        ' . $fullField . ', PRIMARY KEY(id))';
            $this->query($newQuery);
            $newQuery = 'INSERT INTO ' . $table .
                ' (' . $collNames . ') SELECT ' . $collNames . ' FROM t1_backup';
            $this->query($newQuery);
            $newQuery = 'DROP TABLE t1_backup';
            $this->query($newQuery);

            /*@TODO: Get all indexes -> remove indexes -> create index
             * with a new column

            //indexes  must have id
            if(sizeof($indexes) !== 1){
            $this->dropUniqueIndexes($table, $indexes);
            }
            array_push($indexes, $column);
            $this->createUniqueIndexes($table, $indexes);
            */
        } else {
            //just add a new column
            $table = $this->getDbAdapter()->quoteIdentifier($table);
            $query = 'ALTER TABLE ' . $table
                . ' ADD COLUMN ' . $column . $fullField;
            $this->query($query);
        }

        return $this;
    }

    /**
     * dropColumn
     *
     * @param   string   $table
     * @param   string   $name
     * @return  bool
     */
    public function dropColumn($table, $name)
    {
        $fields = $this->getDbAdapter()->describeTable($table);
        $allCols = array();
        $collNames = array();
        foreach ($fields as $field => $options) {
            //skip column
            if ($options['COLUMN_NAME'] === $name)
                continue;
            $collNames[] = $options['COLUMN_NAME'];

            $allCols[$field] = $this->getDbAdapter()->quoteIdentifier($options['COLUMN_NAME']) .
                ' ' . $options['DATA_TYPE'];

            if ($options['LENGTH']) {
                $allCols[$field] .= '(' . $options['LENGTH'] . ')';
            }
            if (!$options['NULLABLE']) {
                $allCols[$field] .= ' NOT NULL ';
            }
            if ($options['DEFAULT']) {
                $allCols[$field] .= " DEFAULT " . $options['DEFAULT'];
            }
        }

        //Full info about columns  exept  names
        $colls = join(',', $allCols);
        //Names of columns
        $collNames = $this->_quoteIdentifierArray($collNames);
        $table = $this->getDbAdapter()->quoteIdentifier($table); //!important

        $newQuery = 'CREATE TEMPORARY TABLE t1_backup(' . $colls . ')';
        $this->query($newQuery);
        $newQuery = 'INSERT INTO t1_backup SELECT ' . $collNames
            . ' FROM ' . $table;
        $this->query($newQuery);
        $newQuery = 'DROP TABLE ' . $table;
        $this->query($newQuery);
        $newQuery = 'Create TABLE ' . $table
            . ' (' . $colls . ', PRIMARY KEY(id))';
        $this->query($newQuery);
        $newQuery = 'INSERT INTO ' . $table .
            ' (' . $collNames . ') SELECT ' . $collNames . ' FROM t1_backup';
        $this->query($newQuery);
        $newQuery = 'DROP TABLE t1_backup';
        $this->query($newQuery);

        return $this;
    }

    /**
     * getCurrentTimestamp
     *
     * @return  string
     */
    public function getCurrentTimestamp()
    {
        return 'now()';
    }

    public function createUniqueIndexes($table, array $columns, $indName = null)
    {
        if ($table && !empty($columns)) {
            if (!$indName) {
                $indName = strtoupper($table . '_' . implode('_', $columns));
            }
            //columns is coma separated string for now
            $quotedColumns = $this->_quoteIdentifierArray($columns);
            $query = 'CREATE UNIQUE INDEX ' . $this->getDbAdapter()->quoteIdentifier($indName) .
                ' ON ' . $this->getDbAdapter()->quoteIdentifier($table) .
                '(' . $quotedColumns . ')';
            $this->query($query);
        } else {
            throw new Core_Exception(
                "Can't create index " . $indName . " on table " . $table
            );
        }
        return $this;
    }

    /**
     * Drop an index on table
     *
     * @param string $indName
     * @return Core_Migration_Adapter_Abstract
     */
    public function dropUniqueIndexes($table, $indName)
    {
        if ($indName) {
            $query = 'DROP INDEX '
                . $this->getDbAdapter()->quoteIdentifier($indName);
            $this->query($query);
        } else {
            throw new Core_Exception("Can't drop index " . $indName);
        }
        return $this;
    }

    /**
     * Quoting array of identifier and converts it to coma separated string
     *
     * @param array $columns
     * @return string
     */
    protected function _quoteIdentifierArray(array $columns)
    {
        $quotedColumns = array();
        foreach ($columns as $value) {
            $quotedColumns[] = $this->getDbAdapter()->quoteIdentifier($value);
        }

        return implode(',', $quotedColumns);
    }
}