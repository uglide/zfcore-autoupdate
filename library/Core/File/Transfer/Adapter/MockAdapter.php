<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * MockAdapter.php
 * Date: 03.10.12
 */
class Core_File_Transfer_Adapter_MockAdapter extends Zend_File_Transfer_Adapter_Abstract
{
    /**
     * Send file
     *
     * @param  mixed $options
     * @return bool
     */
    public function send($options = null)
    {
        return true;
    }

    /**
     * @param array|string $files
     * @param bool $names
     * @param bool $noexception
     * @return array
     */
    protected function _getFiles($files, $names = false, $noexception = false)
    {
        return array();
    }

    /**
     * Receive file
     *
     * @param  mixed $options
     * @return bool
     */
    public function receive($options = null)
    {
        return true;
    }

    /**
     * Is file sent?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isSent($files = null)
    {
        return true;
    }

    /**
     * Is file received?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isReceived($files = null)
    {
        return true;
    }

    /**
     * Has a file been uploaded ?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isUploaded($files = null)
    {
        return true;
    }

    /**
     * Has the file been filtered ?
     *
     * @param array|string|null $files
     * @return bool
     */
    public function isFiltered($files = null)
    {
        return true;
    }

    public function isValid($files = null)
    {
        return true;
    }


}
