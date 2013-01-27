<?php

class Jenkins_JenkinsAPI_Computer
{

    /**
     * @var stdClass
     */
    private $computer;

    /**
     * @var Jenkins_JenkinsAPI
     */
    private $jenkins;


    /**
     * @param stdClass $computer
     * @param Jenkins_JenkinsAPI  $jenkins
     */
    public function __construct($computer, Jenkins_JenkinsAPI $jenkins)
    {
        $this->computer = $computer;
        $this->setJenkins($jenkins);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->computer->displayName;
    }

    /**
     *
     * @return bool
     */
    public function isOffline()
    {
        return (bool)$this->computer->offline;
    }

    /**
     *
     * returns null when computer is launching
     * returns stdClass when computer has been put offline
     *
     * @return null|stdClass
     */
    public function getOfflineCause()
    {
        return $this->computer->offlineCause;
    }

    /**
     *
     * @return Jenkins_JenkinsAPI_Computer
     */
    public function toggleOffline()
    {
        $this->getJenkins()->toggleOfflineComputer($this->getName());

        return $this;
    }

    /**
     *
     * @return Jenkins_JenkinsAPI_Computer
     */
    public function delete()
    {
        $this->getJenkins()->deleteComputer($this->getName());

        return $this;
    }

    /**
     * @return Jenkins_JenkinsAPI
     */
    public function getJenkins()
    {
        return $this->jenkins;
    }

    /**
     * @param Jenkins_JenkinsAPI $jenkins
     *
     * @return Jenkins_JenkinsAPI_Computer
     */
    public function setJenkins(Jenkins_JenkinsAPI $jenkins)
    {
        $this->jenkins = $jenkins;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->getJenkins()->getComputerConfiguration($this->getName());
    }

}
