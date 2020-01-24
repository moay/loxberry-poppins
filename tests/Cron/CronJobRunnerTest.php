<?php

namespace Tests\Cron;

use LoxBerry\Logging\Logger;
use LoxBerryPoppins\Cron\CronJobRunner;
use PHPUnit\Framework\TestCase;
use Tests\Cron\TestableCronJobs\ConfigurableCronJob;

/**
 * Class CronJobRunnerTest.
 */
class CronJobRunnerTest extends TestCase
{
    /** @var Logger|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
    }

    public function testCronJobIsExecutedCorrectly()
    {
        $cronJob = $this->createPartialMock(ConfigurableCronJob::class, ['execute']);
        $cronJob->setInterval(CronJobRunner::INTERVAL_EVERY_MINUTE);
        $cronJob
            ->expects($this->once())
            ->method('execute');

        $runner = new CronJobRunner([$cronJob], $this->logger);
        $runner->executeCronJobs();
    }

    public function testIntervalCronJobIsNotRunOnRebootCronJobExecution()
    {
        $cronJob = $this->createPartialMock(ConfigurableCronJob::class, ['execute']);
        $cronJob->setInterval(CronJobRunner::INTERVAL_EVERY_MINUTE);
        $cronJob
            ->expects($this->never())
            ->method('execute');

        $runner = new CronJobRunner([$cronJob], $this->logger);
        $runner->executeRebootCronJobs();
    }

    public function testRebootCronJobIsExecutedProperly()
    {
        $cronJob = $this->createPartialMock(ConfigurableCronJob::class, ['execute']);
        $cronJob->setInterval(CronJobRunner::INTERVAL_REBOOT);
        $cronJob
            ->expects($this->once())
            ->method('execute');

        $runner = new CronJobRunner([$cronJob], $this->logger);
        $runner->executeRebootCronJobs();
    }

    public function testRebootCronJobIsNotRunOnIntervalCronJobExecution()
    {
        $cronJob = $this->createPartialMock(ConfigurableCronJob::class, ['execute']);
        $cronJob->setInterval(CronJobRunner::INTERVAL_REBOOT);
        $cronJob
            ->expects($this->never())
            ->method('execute');

        $runner = new CronJobRunner([$cronJob], $this->logger);
        $runner->executeCronJobs();
    }

    /**
     * @dataProvider timeBasedIntervals
     */
    public function testIntervalsAreRespectedWhenExecuting(int $interval)
    {
        $cronJob = $this->createPartialMock(ConfigurableCronJob::class, ['execute']);
        $cronJob->setInterval($interval);
        if (0 === round(time() / 60) % $interval) {
            $cronJob
                ->expects($this->once())
                ->method('execute');
        } else {
            $cronJob
                ->expects($this->never())
                ->method('execute');
        }

        $runner = new CronJobRunner([$cronJob], $this->logger);
        $runner->executeCronJobs();
    }

    public function timeBasedIntervals()
    {
        return array_map(function ($interval) {
            return [$interval];
        }, CronJobRunner::KNOWN_TIMEBASED_INTERVALS);
    }
}
