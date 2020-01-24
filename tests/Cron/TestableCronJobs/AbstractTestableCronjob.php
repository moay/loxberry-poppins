<?php

namespace Tests\Cron\TestableCronJobs;

use LoxBerryPoppins\Cron\AbstractCronJob;

/**
 * Class AbstractTestableCronjob.
 */
abstract class AbstractTestableCronjob extends AbstractCronJob
{
    public function execute()
    {
        $this->getLogger()->debug(self::class);
    }
}
