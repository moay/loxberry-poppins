<?php

namespace LoxBerryPoppins\Frontend\Twig\Extensions;

use LoxBerry\ConfigurationParser\MiniserverInformation;
use LoxBerry\ConfigurationParser\SystemConfigurationParser;
use LoxBerry\System\PathProvider;
use LoxBerry\System\Paths;
use LoxBerry\System\Plugin\PluginDatabase;
use LoxBerry\System\Plugin\PluginInformation;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class TemplateElements.
 */
class TemplateElements extends AbstractExtension
{
    /** @var PathProvider */
    private $pathProvider;

    /** @var string */
    private $templateDirectory;

    /** @var string */
    private $packageName;

    /** @var SystemConfigurationParser */
    private $systemConfiguration;

    /** @var PluginInformation */
    private $pluginInformation;

    /** @var Translations */
    private $translations;

    /**
     * TemplateElements constructor.
     *
     * @param PathProvider              $pathProvider
     * @param SystemConfigurationParser $systemConfiguration
     * @param PluginDatabase            $pluginDatabase
     * @param Translations              $translations
     * @param string                    $packageName
     */
    public function __construct(
        PathProvider $pathProvider,
        SystemConfigurationParser $systemConfiguration,
        PluginDatabase $pluginDatabase,
        Translations $translations,
        $packageName
    ) {
        $this->pathProvider = $pathProvider;
        $this->templateDirectory = rtrim($this->pathProvider->getPath(Paths::PATH_SYSTEM_TEMPLATE), '/');
        $this->packageName = $packageName;
        $this->systemConfiguration = $systemConfiguration;
        $this->pluginInformation = $pluginDatabase->getPluginInformation($packageName);
        $this->translations = $translations;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'logFileButton',
                [$this, 'getLogFileButton'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'logListUrl',
                [$this, 'getLogListUrl'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'logListButton',
                [$this, 'getLogListButton'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'logList',
                [$this, 'getLogList'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'miniserverSelect',
                [$this, 'getMiniserverSelect'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'logLevelSelect',
                [$this, 'getLogLevelSelect'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string      $logGroup
     * @param string|null $label
     * @param bool        $mini
     * @param string|null $icon
     *
     * @return string
     */
    public function getLogFileButton(string $logGroup, ?string $label = null, bool $mini = true, ?string $icon = 'action'): string
    {
        return sprintf(
            '<a data-role="button" href="/admin/system/tools/logfile.cgi?name=%s&package=%s&header=html&format=template" target="_blank" data-inline="true" data-mini="%s" data-icon="%s">%s</a>',
            $logGroup,
            $this->packageName,
            $mini ? 'true' : 'false',
            $icon,
            $this->translations->translate($label ?? 'COMMON.BUTTON_LOGFILE')
        );
    }

    /**
     * @param string $logGroup
     *
     * @return string
     */
    public function getLogListUrl(string $logGroup): string
    {
        return sprintf(
            '/admin/system/logmanager.cgi?package=%s&name=%s',
            $this->packageName,
            $logGroup
        );
    }

    /**
     * @param string      $logGroup
     * @param string|null $label
     * @param bool        $mini
     * @param string|null $icon
     *
     * @return string
     */
    public function getLogListButton(string $logGroup, ?string $label = null, bool $mini = true, ?string $icon = 'action'): string
    {
        return sprintf(
            '<a data-role="button" href="%s" target="_blank" data-inline="true" data-mini="%s" data-icon="%s">%s</a>',
            $this->getLogListUrl($logGroup),
            $mini ? 'true' : 'false',
            $icon,
            $this->translations->translate($label ?? 'COMMON.BUTTON_LOGFILE_LIST')
        );
    }

    /**
     * @param string $logGroup
     *
     * @return string
     */
    public function getLogList(string $logGroup): string
    {
        $url = sprintf(
            'http://localhost:%s%s&header=none',
            $this->systemConfiguration->getWebserverPort(),
            $this->getLogListUrl($logGroup)
        );

        return file_get_contents($url);
    }

    /**
     * @param string|null $label
     * @param string|null $fieldName
     * @param int|null    $selected
     * @param bool        $mini
     *
     * @return string
     */
    public function getMiniserverSelect(
        ?string $label = null,
        ?string $fieldName = 'select_miniserver',
        ?string $selectedIp = null,
        bool $mini = true
    ): string {
        if (0 === $this->systemConfiguration->getNumberOfMiniservers()) {
            return '<div>No Miniservers defined</div>';
        }
        if (
            1 === $this->systemConfiguration->getNumberOfMiniservers() &&
            '' === $this->systemConfiguration->getMiniservers()[0]->getIpAddress()
        ) {
            return '<div>Please configure your miniserver properly</div>';
        }

        $selectOptions = array_map(function (MiniserverInformation $miniserver) use ($selectedIp) {
            return sprintf(
                '<option value="%s" %s>%s</option>',
                $miniserver->getIpAddress(),
                null !== $selectedIp && $selectedIp === $miniserver->getIpAddress() ? 'selected="selected"' : '',
                $miniserver->getName().' ('.$miniserver->getIpAddress().')'
            );
        }, $this->systemConfiguration->getMiniservers());

        return sprintf(
            '<div class="ui-field-contain">%s<select name="%s" id="%s" data-mini="%s">%s</select></div>',
            null !== $label ? '<label for="'.$fieldName.'">'.$this->translations->translate($label).'</label>' : '',
            $fieldName,
            $fieldName,
            $mini ? 'true' : 'false',
            implode(PHP_EOL, $selectOptions)
        );
    }

    /**
     * @param string|null $label
     * @param string|null $fieldName
     * @param bool        $mini
     *
     * @return string
     */
    public function getLogLevelSelect(?string $label = null, ?string $fieldName = 'select_loglevel', bool $mini = true): string
    {
        if (!$this->pluginInformation->isLogLevelsEnabled()) {
            return '';
        }

        $output = '<div data-role="fieldcontain">';

        if ('' !== $label) {
            $output .= sprintf(
                '<label for="'.$fieldName.'" style="display:inline-block;">%s</label>',
                $this->translations->translate($label ?? 'PLUGININSTALL.UI_LABEL_LOGGING_LEVEL')
            );
        }

        $output .= '<fieldset data-role="controlgroup" data-mini="'.($mini ? 'true' : 'false').'" style="width:200px;">';

        $output .= <<<EOF
	
	<select name="{$fieldName}" id="{$fieldName}" data-mini="{($mini ? 'true' : 'false')}">
		<option value="0">{$this->translations->translate('PLUGININSTALL.UI_LOG_0_OFF')}</option>
		<option value="3">{$this->translations->translate('PLUGININSTALL.UI_LOG_3_ERRORS')}</option>
		<option value="4">{$this->translations->translate('PLUGININSTALL.UI_LOG_4_WARNING')}</option>
		<option value="6">{$this->translations->translate('PLUGININSTALL.UI_LOG_6_INFO')}</option>
		<option value="7">{$this->translations->translate('PLUGININSTALL.UI_LOG_7_DEBUG')}</option>
	</select>
	</fieldset>
	</div>
	
	<script>
	$(document).ready( function()
	{
		$("#{$fieldName}").val('{$this->pluginInformation->getLogLevel()}').change();
	});
		
	$("#{$fieldName}").change(function(){
		var val = $(this).val();
		post_value('plugin-loglevel', '{$this->pluginInformation->getChecksum()}', val); 
	});
	
	function post_value (action, pluginmd5, value)
	{
	$.post ( '/admin/system/tools/ajax-config-handler.cgi', 
		{ 	action: action,
			value: value,
			pluginmd5: pluginmd5
		});
	}

	</script>
EOF;

        return $output;
    }
}
