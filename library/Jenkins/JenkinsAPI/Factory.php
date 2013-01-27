<?php

class Jenkins_JenkinsAPI_Factory
{

    /**
     * @param string $url
     *
     * @return Jenkins_JenkinsAPI
     */
    public function build($url)
    {
        return new Jenkins_JenkinsAPI($url);
    }


}
