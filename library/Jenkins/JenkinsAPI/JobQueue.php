<?php

class Jenkins_JenkinsAPI_JobQueue
{
    /**
     * @var stdClass
     */
    private $jobQueue;


    /**
     * @var Jenkins_JenkinsAPI
     */
    protected $jenkins;

    /**
     * @param stdClass $jobQueue
     * @param Jenkins_JenkinsAPI  $jenkins
     */
    public function __construct($jobQueue, Jenkins_JenkinsAPI $jenkins)
    {
        $this->jobQueue = $jobQueue;
        $this->setJenkins($jenkins);
    }

    /**
     * @return array
     */
    public function getInputParameters()
    {
        $parameters = array();

        if (!property_exists($this->jobQueue->actions[0], 'parameters')) {
            return $parameters;
        }

        foreach ($this->jobQueue->actions[0]->parameters as $parameter) {
            $parameters[$parameter->name] = $parameter->value;
        }

        return $parameters;
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return $this->jobQueue->task->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->jobQueue->id;
    }

    /**
     * @return void
     */
    public function cancel()
    {
        $this->getJenkins()->cancelQueue($this);
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
     */
    public function setJenkins(Jenkins_JenkinsAPI $jenkins)
    {
        $this->jenkins = $jenkins;

        return $this;
    }

}
