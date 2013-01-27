<?php

class Jenkins_JenkinsAPI_Queue
{
    /**
     * @var stdClass
     */
    private $queue;

    /**
     * @var Jenkins_JenkinsAPI
     */
    protected $jenkins;

    /**
     * @param stdClass $queue
     * @param Jenkins_JenkinsAPI  $jenkins
     */
    public function __construct($queue, Jenkins_JenkinsAPI $jenkins)
    {
        $this->queue = $queue;
        $this->setJenkins($jenkins);
    }

    /**
     * @return array
     */
    public function getJobQueues()
    {
        $jobs = array();

        foreach ($this->queue->items as $item) {
            $jobs[] = new Jenkins_JenkinsAPI_JobQueue($item, $this->getJenkins());
        }

        return $jobs;
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
     * @return \Jenkins_JenkinsAPI_Queue
     */
    public function setJenkins(Jenkins_JenkinsAPI $jenkins)
    {
        $this->jenkins = $jenkins;

        return $this;
    }

}
