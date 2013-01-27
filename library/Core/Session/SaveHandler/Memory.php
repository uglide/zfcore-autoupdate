<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Memory.php
 * Date: 10.08.12
 */
class Core_Session_SaveHandler_Memory implements Zend_Session_SaveHandler_Interface
{
    protected $data = array();
    protected $name = null;

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name)
    {
        $this->data[$name] = array();
        $this->name = $name;
    }

    /**
     * Close Session - free resources
     *
     */
    public function close()
    {
        $this->data = array();
    }

    /**
     * Read session data
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $res = '';

        if (isset($this->data[$this->name][$id])) {
            $res = $this->data[$this->name][$id];
        }

        return $res;
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     */
    public function write($id, $data)
    {
        if (null == $this->name) {
            $this->name = 'default';
        }

        $this->data[$this->name][$id] = $data;
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id)
    {
        if (isset($this->data[$this->name][$id])) {
            unset($this->data[$this->name][$id]);
        }
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
    }

}
