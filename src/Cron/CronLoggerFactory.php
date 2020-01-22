<?php

namespace LoxBerryPoppins\Cron;

use LoxBerry\Logging\LoggerFactory;
use LoxBerry\System\Paths;
use LoxBerry\System\Plugin\PluginPathProvider;

/**
 * Class CronLoggerFactory.
 */
class CronLoggerFactory
{
    const LOG_NAME = 'Cron';

    /** @var LoggerFactory */
    private $loggerFactory;

    /** @var string */
    private $packageName;

    /** @var PluginPathProvider */
    private $pathProvider;

    /**
     * CronLoggerFactory constructor.
     *
     * @param LoggerFactory      $loggerFactory
     * @param PluginPathProvider $pathProvider
     * @param $packageName
     */
    public function __construct(
        LoggerFactory $loggerFactory,
        PluginPathProvider $pathProvider,
        $packageName
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->packageName = $packageName;
        $this->pathProvider = $pathProvider;
        $this->pathProvider->setPluginName($packageName);
    }

    /**
     * @return \LoxBerry\Logging\Logger
     */
    public function __invoke()
    {
        $logFileDirectory = $this->pathProvider->getPath(Paths::PATH_PLUGIN_LOG);

        return $this->loggerFactory->__invoke(
            self::LOG_NAME,
            $this->packageName,
            $logFileDirectory.'/'.date('Ymd-His_').'cron.log'
        );
    }
}
