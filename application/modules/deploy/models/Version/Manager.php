<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Manager.php
 * Date: 21.12.12
 */
class Deploy_Model_Version_Manager extends Core_Model_Manager
{
    const VERIFY_TABLE = 'verified_versions';

    /**
     * @param $versionID
     * @param $verifierID
     *
     * @return int
     */
    public function verifyVersion($versionID, $verifierID)
    {
        if ($this->isVersionVerified($versionID)) {
            return false;
        }

        $db = $this->getDbTable()->getAdapter();

        return $db->insert(
            self::VERIFY_TABLE,
            array(
                'versionID' => $versionID,
                'verifiedBy' => $verifierID
            )
        );
    }

    /**
     * @param string $targetEnvType
     *
     * @return array
     */
    public function getVerifiedVersions($targetEnvType = Deploy_Model_Environment::TYPE_LIVE)
    {
        $db = $this->getDbTable()->getAdapter();

        $sourceEnvType = ($targetEnvType == Deploy_Model_Environment::TYPE_LIVE)?
            Deploy_Model_Environment::TYPE_STAGE : Deploy_Model_Environment::TYPE_TEST;

        $revisions = $db->fetchCol(
            $db->select()
                ->from(
                    array('vt' => self::VERIFY_TABLE),
                    array()
                )
                ->joinInner(
                    array('v' => 'version'),
                    'v.id = vt.versionID',
                    array('revision')
                )
                ->joinInner(
                    array('e' => 'environments'),
                    'e.id = v.environmentID'
                )
                ->order('v.created DESC')
                ->where('e.type = ?', $sourceEnvType)
        );

        return $revisions ? $revisions : array();
    }

    /**
     * @param $versionID
     *
     * @return bool
     */
    public function isVersionVerified($versionID)
    {
        $db = $this->getDbTable()->getAdapter();

        $res = $db->fetchRow(
            $db->select()
                ->from(self::VERIFY_TABLE)
                ->where('versionID = ?', $versionID)
                ->limit(1)
        );

        return !empty($res);
    }

    /**
     * @param $revision
     * @param $type
     *
     * @return bool
     * @throws Core_Exception
     */
    public function isRevisionVerifiedForEnvironment($revision, $type)
    {
        if ($type != Deploy_Model_Environment::TYPE_LIVE) {
            throw new Core_Exception(
                "Only live environments use verified versions for update"
            );
        } else {
            $type = Deploy_Model_Environment::TYPE_STAGE;
        }

        $db = $this->getDbTable()->getAdapter();

        $subSelect = $db->select()
            ->from('version', array('id'))
            ->where('environmentID IN (SELECT id FROM environments WHERE `type` = ?) ', $type)
            ->where('revision = ?', $revision)
            ->limit(1);

        $res = $db->fetchRow(
            $db->select()
                ->from(self::VERIFY_TABLE)
                ->where(
                    'versionID = (' . (string)$subSelect . ') '
                )
                ->limit(1)
        );

        return !empty($res);
    }

}
