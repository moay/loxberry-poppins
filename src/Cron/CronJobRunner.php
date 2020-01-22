<?php

namespace LoxBerryPoppins\Cron;

use LoxBerry\Logging\Logger;

/**
 * Class CronJobRunner.
 */
class CronJobRunner
{
    const INTERVAL_EVERY_MINUTE = 1;
    const INTERVAL_EVERY_TWO_MINUTES = 2;
    const INTERVAL_EVERY_THREE_MINUTES = 3;
    const INTERVAL_EVERY_FIVE_MINUTES = 5;
    const INTERVAL_EVERY_TEN_MINUTES = 10;
    const INTERVAL_EVERY_FIFTEEN_MINUTES = 15;
    const INTERVAL_EVERY_TWENTY_MINUTES = 20;
    const INTERVAL_EVERY_HALF_HOUR = 30;
    const INTERVAL_EVERY_HOUR = 60;
    const INTERVAL_EVERY_DAY = self::INTERVAL_EVERY_HOUR * 24;
    const INTERVAL_EVERY_WEEK = self::INTERVAL_EVERY_DAY * 7;
    const INTERVAL_EVERY_MONTH = self::INTERVAL_EVERY_DAY * 30;
    const INTERVAL_EVERY_YEAR = self::INTERVAL_EVERY_DAY * 365;

    const INTERVAL_REBOOT = 'reboot';

    const KNOWN_TIMEBASED_INTERVALS = [
        self::INTERVAL_EVERY_MINUTE,
        self::INTERVAL_EVERY_TWO_MINUTES,
        self::INTERVAL_EVERY_THREE_MINUTES,
        self::INTERVAL_EVERY_FIVE_MINUTES,
        self::INTERVAL_EVERY_TEN_MINUTES,
        self::INTERVAL_EVERY_FIFTEEN_MINUTES,
        self::INTERVAL_EVERY_TWENTY_MINUTES,
        self::INTERVAL_EVERY_HALF_HOUR,
        self::INTERVAL_EVERY_HOUR,
        self::INTERVAL_EVERY_WEEK,
        self::INTERVAL_EVERY_MONTH,
        self::INTERVAL_EVERY_YEAR,
    ];

    /** @var CronJobInterface[] */
    private $cronJobs = [];

    /** @var Logger */
    private $logger;

    /** @var bool */
    private $logStarted = false;

    /**
     * CronJobRunner constructor.
     *
     * @param iterable $cronJobs
     * @param Logger   $cronLogger
     */
    public function __construct(iterable $cronJobs, $cronLogger)
    {
        foreach ($cronJobs as $cronJob) {
            if (!$cronJob instanceof CronJobInterface) {
                throw new \InvalidArgumentException('Inject cron jobs only');
            }
            $this->cronJobs[] = $cronJob;
        }
        $this->logger = $cronLogger;
    }

    public function executeCronJobs()
    {
        foreach ($this->cronJobs as $cronJob) {
            if (!in_array($cronJob->getInterval(), self::KNOWN_TIMEBASED_INTERVALS)) {
                continue;
            }
            if (0 === round(time() / 60) % $cronJob->getInterval()) {
                if (!$this->logStarted) {
                    $this->logger->logStart('regular');
                }
                $this->executeCronJob($cronJob);
            }
        }
    }

    public function executeRebootCronJobs()
    {
        foreach ($this->cronJobs as $cronJob) {
            if (self::INTERVAL_REBOOT !== $cronJob->getInterval()) {
                continue;
            }
            if (!$this->logStarted) {
                $this->logger->logStart('reboot');
            }
            $this->executeCronJob($cronJob);
        }
    }

    /**
     * @param CronJobInterface $cronJob
     */
    private function executeCronJob(CronJobInterface $cronJob)
    {
        if ($cronJob instanceof AbstractCronJob) {
            $cronJob->setLogger($this->logger);
        }

        try {
            $this->logger->log('Executing CronJob '.get_class($cronJob));
            $cronJob->execute();
            $this->logger->success('Finished execution of CronJob '.get_class($cronJob));
        } catch (\Throwable $exception) {
            $this->logger->error('Error during cron job execution of CronJob '.get_class($cronJob));
            array_map([$this->logger, 'debug'], $this->formatTraceStack($exception));
        }
    }

    /**
     * @param \Throwable $exception
     *
     * @return array
     */
    private function formatTraceStack(\Throwable $exception): array
    {
        $stack = [sprintf(
            '%s - thrown in file "%s" on line %s',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ), '', 'Stacktrace:'];

        foreach ($exception->getTrace() as $i => $traceLine) {
            $stack[] = sprintf(
                '#%s - %s L%s (%s)',
                $i,
                $traceLine['file'],
                $traceLine['line'],
                array_key_exists('class', $traceLine) ?
                    $traceLine['class'].($traceLine['type'] ?? '::').$traceLine['function'] :
                    'Function '.$traceLine['function']
            );
        }

        return $stack;
    }
}
