<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>
 * Date: 03.11.12
 * Time: 17:19
 */

class Deploy_Model_Version extends Core_Db_Table_Row_Abstract
{
    public function getMigrations()
    {
        return unserialize($this->_data['loadedMigrations']);
    }
}
