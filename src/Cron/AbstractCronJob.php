<?php

namespace LoxBerryPoppins\Cron;

use LoxBerry\Logging\Logger;

/**
 * Class AbstractCronJob.
 */
abstract class AbstractCronJob implements CronJobInterface
{
    /** @var Logger */
    private $logger;

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }
}
