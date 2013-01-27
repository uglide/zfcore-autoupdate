<?php

class Jenkins_JenkinsAPI_Executor
{

    /**
     * @var stdClass
     */
    private $executor;

    /**
     * @var Jenkins_JenkinsAPI
     */
    protected $jenkins;

    /**
     * @var string
     */
    protected $computer;

    /**
     * @param stdClass $executor
     * @param string   $computer
     * @param \Jenkins_JenkinsAPI $jenkins
     */
    public function __construct($executor, $computer, Jenkins_JenkinsAPI $jenkins)
    {
        $this->executor = $executor;
        $this->computer = $computer;
        $this->setJenkins($jenkins);
    }

    /**
     * @return string
     */
    public function getComputer()
    {
        return $this->computer;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        return $this->executor->progress;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->executor->number;
    }


    /**
     * @return int|null
     */
    public function getBuildNumber()
    {
        $number = null;
        if (isset($this->executor->currentExecutable)) {
            $number = $this->executor->currentExecutable->number;
        }

        return $number;
    }

    /**
     * @return null|string
     */
    public function getBuildUrl()
    {
        $url = null;
        if (isset($this->executor->currentExecutable)) {
            $url = $this->executor->currentExecutable->url;
        }

        return $url;
    }

    /**
     * @return void
     */
    public function stop()
    {
        $this->getJenkins()->stopExecutor($this);
    }

    /**
     * @return \Jenkins_JenkinsAPI
     */
    public function getJenkins()
    {
        return $this->jenkins;
    }

    /**
     * @param \Jenkins_JenkinsAPI $jenkins
     *
     * @return Jenkins_JenkinsAPI_Job
     */
    public function setJenkins(Jenkins_JenkinsAPI $jenkins)
    {
        $this->jenkins = $jenkins;

        return $this;
    }

}
