<?php

namespace Tests\Cron\TestableCronJobs;

/**
 * Class ConfigurableCronJob.
 */
class ConfigurableCronJob extends AbstractTestableCronjob
{
    /** @var int */
    private $interval;

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }
}
