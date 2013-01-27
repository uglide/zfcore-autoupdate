<?php

class Jenkins_JenkinsAPI_Build
{
    /**
     * @var string
     */
    const FAILURE = 'FAILURE';

    /**
     * @var string
     */
    const SUCCESS = 'SUCCESS';

    /**
     * @var string
     */
    const RUNNING = 'RUNNING';

    /**
     * @var string
     */
    const WAITING = 'WAITING';

    /**
     * @var string
     */
    const UNSTABLE = 'UNSTABLE';

    /**
     * @var string
     */
    const ABORTED = 'ABORTED';

    /**
     * @var stdClass
     */
    private $build;

    /**
     * @var Jenkins_JenkinsAPI
     */
    private $jenkins;


    /**
     * @param stdClass   $build
     * @param Jenkins_JenkinsAPI $jenkins
     */
    public function __construct($build, Jenkins_JenkinsAPI $jenkins)
    {
        $this->build = $build;
        $this->setJenkins($jenkins);
    }

    /**
     * @return array
     */
    public function getInputParameters()
    {
        $parameters = array();

        if (!property_exists($this->build->actions[0], 'parameters')) {
            return $parameters;
        }

        foreach ($this->build->actions[0]->parameters as $parameter) {
            $parameters[$parameter->name] = $parameter->value;
        }

        return $parameters;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        //division par 1000 => pas de millisecondes
        return $this->build->timestamp / 1000;
    }


    /**
     * @return int
     */
    public function getDuration()
    {
        //division par 1000 => pas de millisecondes
        return $this->build->duration / 1000;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->build->number;
    }

    /**
     * @return null|int
     */
    public function getProgress()
    {
        $progress = null;
        if (null !== ($executor = $this->getExecutor())) {
            $progress = $executor->getProgress();
        }

        return $progress;
    }

    /**
     * @return float|null
     */
    public function getEstimatedDuration()
    {
        //since version 1.461 estimatedDuration is displayed in jenkins's api
        //we can use it witch is more accurate than calcule ourselves
        //but older versions need to continue to work, so in case of estimated
        //duration is not found we fallback to calcule it.
        if (property_exists($this->build, 'estimatedDuration')) {
            return $this->build->estimatedDuration / 1000;
        }

        $duration = null;
        $progress = $this->getProgress();
        if (null !== $progress && $progress >= 0) {
            $duration = ceil((time() - $this->getTimestamp()) / ($progress / 100));
        }

        return $duration;
    }


    /**
     * Returns remaining execution time (seconds)
     *
     * @return int|null
     */
    public function getRemainingExecutionTime()
    {
        $remaining = null;
        if (null !== ($estimatedDuration = $this->getEstimatedDuration())) {
            //be carefull because time from JK server could be different
            //of time from Jenkins_JenkinsAPI server
            //but i didn't find a timestamp given by Jenkins_JenkinsAPI api

            $remaining = $estimatedDuration - (time() - $this->getTimestamp());
        }

        return $remaining;
    }

    /**
     * @return null|string
     */
    public function getResult()
    {
        $result = null;
        switch ($this->build->result) {
            case 'FAILURE':
                $result = Jenkins_JenkinsAPI_Build::FAILURE;
                break;
            case 'SUCCESS':
                $result = Jenkins_JenkinsAPI_Build::SUCCESS;
                break;
            case 'UNSTABLE':
                $result = Jenkins_JenkinsAPI_Build::UNSTABLE;
                break;
            case 'ABORTED':
                $result = Jenkins_JenkinsAPI_Build::ABORTED;
                break;
            case 'WAITING':
                $result = Jenkins_JenkinsAPI_Build::WAITING;
                break;
            default:
                $result = Jenkins_JenkinsAPI_Build::RUNNING;
                break;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->build->url;
    }

    /**
     * @return Jenkins_JenkinsAPI_Executor|null
     */
    public function getExecutor()
    {
        if (!$this->isRunning()) {
            return null;
        }

        $runExecutor = null;
        foreach ($this->getJenkins()->getExecutors() as $executor) {
            /** @var Jenkins_JenkinsAPI_Executor $executor */

            if ($this->getUrl() === $executor->getBuildUrl()) {
                $runExecutor = $executor;
            }
        }

        return $runExecutor;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return Jenkins_JenkinsAPI_Build::RUNNING === $this->getResult();
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

    public function getBuiltOn()
    {
        return $this->build->builtOn;
    }

}
