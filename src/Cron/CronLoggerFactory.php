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
    private $pluginName;

    /** @var PluginPathProvider */
    private $pathProvider;

    /**
     * CronLoggerFactory constructor.
     *
     * @param LoggerFactory      $loggerFactory
     * @param PluginPathProvider $pathProvider
     * @param $pluginName
     */
    public function __construct(
        LoggerFactory $loggerFactory,
        PluginPathProvider $pathProvider,
        $pluginName
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->pluginName = $pluginName;
        $this->pathProvider = $pathProvider;
        $this->pathProvider->setPluginName($pluginName);
    }

    /**
     * @return \LoxBerry\Logging\Logger
     */
    public function __invoke()
    {
        $logFileDirectory = $this->pathProvider->getPath(Paths::PATH_PLUGIN_LOG);

        return $this->loggerFactory->__invoke(
            self::LOG_NAME,
            $this->pluginName,
            $logFileDirectory.'/'.date('Ymd-His_').'cron.log'
        );
    }
}
