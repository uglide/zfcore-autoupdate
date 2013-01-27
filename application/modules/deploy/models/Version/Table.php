<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>
 * Date: 27.10.12
 * Time: 16:51
 */

class Deploy_Model_Version_Table extends Core_Db_Table_Abstract
{
    /** Table name */
    protected $_name = 'version';

    /** Primary Key */
    protected $_primary = 'id';

    /** Row Class */
    protected $_rowClass = 'Deploy_Model_Version';

}
