<?php

namespace LoxBerryPoppins\Cron;

interface CronJobInterface
{
    public function getInterval();

    public function execute();
}
