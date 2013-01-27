<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Repository.php
 * Date: 30.10.12
 */
class Deploy_Model_Repository
{
    /**
     * in seconds
     */
    const SHELL_EXECUTION_TIMEOUT = 30;

    private $_path;

    /**
     * TODO: add logger support
     * @param $path
     */
    public function __construct($path)
    {
        $this->_path = $path;

        chdir($this->_path);
    }

    public function check()
    {
        return is_dir($this->_path . DIRECTORY_SEPARATOR . '.hg');
    }

    public function getCurrentVersion($useTagsAsVersions = true)
    {
        chdir($this->_path);
        $version = trim((string)@shell_exec('hg identify -it'));

        if (!$version) {
            return null;
        }

        $version = explode(' ', $version);

        $hash = $version[0];
        $tag = (isset($version[1])) ? $version[1] : '';

        if (!empty($tag) && $tag != 'tip' && $useTagsAsVersions) {
            $revision = $tag;
        } else {
            $revision = str_replace('+', '', $hash);
        }

        return $revision;
    }

    public function update($revision)
    {
        chdir($this->_path);

        $res = Core_System::shellExec(
            'hg up -C '. $revision,
            self::SHELL_EXECUTION_TIMEOUT
        );

        return $res;
    }

    public function pull($revision = '')
    {
        chdir($this->_path);

        $res = Core_System::shellExec(
            'hg pull '. $revision, self::SHELL_EXECUTION_TIMEOUT
        );

        return $res;
    }

    public function checkCentralRepo()
    {
        chdir($this->_path);
        ob_start();
        @passthru('hg showconfig -u ', $result);
        $configRawData=ob_get_contents();
        ob_end_clean();

        if (!$configRawData) return false;

        $matches = array();
        preg_match('/paths\.default=(.+)/i', (string)$configRawData, $matches);

        if (!$defaultPath = $matches[1]) return false;

        $sh = curl_init($defaultPath);
        curl_setopt($sh, CURLOPT_NOBODY, true);
        curl_setopt($sh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($sh, CURLOPT_TIMEOUT, 20);
        curl_exec($sh);
        $respStatus = curl_getinfo($sh, CURLINFO_HTTP_CODE);
        curl_close($sh);

        return in_array($respStatus, array(401, 302));
    }

    /**
     * @param $revision
     * @return bool
     */
    public function isRevisionExists($revision)
    {
        chdir($this->_path);
        $localRev = (int)@shell_exec('hg id -n -r ' . $revision);

        return $localRev > 0;
    }

    /**
     * @param $revision
     * @return string
     */
    public function getRevisionFileList($revision)
    {
        chdir($this->_path);
        $fileList = @shell_exec('hg manifest -r ' . $revision);

        return $fileList;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $this->pull();

        $tagsRaw = @shell_exec('hg tags ');

        if (!$tagsRaw) return array();

        $matches = array();

        preg_match_all('/([a-z0-9\.:_]*)\s+([0-9]+:([0-9a-z]+))/i', $tagsRaw, $matches);

        return array_combine($matches[3], $matches[1]);
    }

    /**
     * @return string
     */
    public function getCurrentBranchName()
    {
        chdir($this->_path);

        return trim(@shell_exec('hg identify -b'));
    }



}
