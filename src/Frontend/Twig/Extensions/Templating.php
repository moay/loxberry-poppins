<?php

namespace LoxBerryPoppins\Frontend\Twig\Extensions;

use LoxBerry\ConfigurationParser\SystemConfigurationParser;
use LoxBerry\System\Localization\LanguageDeterminator;
use LoxBerry\System\PathProvider;
use LoxBerry\System\Paths;
use LoxBerryPoppins\Frontend\Help\HelpRenderer;
use LoxBerryPoppins\Frontend\Twig\Utility\NavigationBarBuilder;
use LoxBerryPoppins\Frontend\Twig\Utility\TranslatedSystemTemplateLoader;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class Templating.
 */
class Templating extends AbstractExtension
{
    /** @var PathProvider */
    private $pathProvider;

    /** @var string */
    private $templateDirectory;

    /** @var SystemConfigurationParser */
    private $systemConfigurationParser;

    /** @var LanguageDeterminator */
    private $languageDeterminator;

    /** @var NavigationBarBuilder */
    private $navigationBarBuilder;

    /** @var TranslatedSystemTemplateLoader */
    private $systemTemplateLoader;
    /** @var HelpRenderer */
    private $helpRenderer;

    /**
     * Templating constructor.
     *
     * @param PathProvider                   $pathProvider
     * @param SystemConfigurationParser      $systemConfigurationParser
     * @param LanguageDeterminator           $languageDeterminator
     * @param NavigationBarBuilder           $navigationBarBuilder
     * @param TranslatedSystemTemplateLoader $systemTemplateLoader
     * @param HelpRenderer                   $helpRenderer
     */
    public function __construct(
        PathProvider $pathProvider,
        SystemConfigurationParser $systemConfigurationParser,
        LanguageDeterminator $languageDeterminator,
        NavigationBarBuilder $navigationBarBuilder,
        TranslatedSystemTemplateLoader $systemTemplateLoader,
        HelpRenderer $helpRenderer
    ) {
        $this->pathProvider = $pathProvider;
        $this->templateDirectory = rtrim($this->pathProvider->getPath(Paths::PATH_SYSTEM_TEMPLATE), '/');
        $this->systemConfigurationParser = $systemConfigurationParser;
        $this->languageDeterminator = $languageDeterminator;
        $this->navigationBarBuilder = $navigationBarBuilder;
        $this->systemTemplateLoader = $systemTemplateLoader;
        $this->helpRenderer = $helpRenderer;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'loxBerryHtmlHead',
                [$this, 'htmlHead'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'loxBerryHtmlFoot',
                [$this, 'htmlFoot'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'loxBerryPageStart',
                [$this, 'pageStart'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'loxBerryPageEnd',
                [$this, 'pageEnd'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string|null $pageTitle
     * @param string|null $htmlHead
     *
     * @return string
     */
    public function htmlHead(?string $pageTitle = null, ?string $htmlHead = ''): string
    {
        $templateFile = $this->templateDirectory.'/head.html';
        $template = $this->systemTemplateLoader->loadTranslatedFile($templateFile);

        return $this->replaceVariables($template, [
            'TEMPLATETITLE' => $this->getPrintedPageTitle($pageTitle),
            'LANG' => $this->languageDeterminator->getLanguage(),
            'HTMLHEAD' => $htmlHead,
        ]);
    }

    /**
     * @return string
     */
    public function htmlFoot(): string
    {
        $templateFile = $this->templateDirectory.'/foot.html';
        $template = $this->systemTemplateLoader->loadTranslatedFile($templateFile);

        return $this->replaceVariables($template, [
            'LANG' => $this->languageDeterminator->getLanguage(),
        ]);
    }

    /**
     * @param null        $pageTitle
     * @param string|null $navBar
     * @param bool        $hidePanels
     *
     * @return string
     */
    public function pageStart(Environment $twig, ?string $pageTitle = null, ?string $navBar = null, bool $hidePanels = false): string
    {
        $templateFile = $this->templateDirectory.($hidePanels ? '/pagestart_nopanels.html' : '/pagestart.html');
        $template = $this->systemTemplateLoader->loadTranslatedFile($templateFile, ['HEADER']);
        $variables = [
            'TEMPLATETITLE' => $this->getPrintedPageTitle($pageTitle),
            'HELPLINK' => $this->helpRenderer->getHelpUrl(),
            'PAGE' => 'test',
            'LANG' => $this->languageDeterminator->getLanguage(),
            'TOPNAVBAR' => $this->navigationBarBuilder->getNavigationBarHtml(),
            'NAVBARJS' => '',
        ];
        $helpContents = $this->helpRenderer->getHelpContents($twig);

        if (null !== $helpContents) {
            $variables['HELPTEXT'] = $helpContents;
        }

        return $this->replaceVariables($template, $variables);
    }

    /**
     * @return string
     */
    public function pageEnd(): string
    {
        $templateFile = $this->templateDirectory.'/pageend.html';
        $template = $this->systemTemplateLoader->loadTranslatedFile($templateFile, ['POWER', 'UPDATES', 'HEADER']);

        return $this->replaceVariables($template, [
            'LANG' => $this->languageDeterminator->getLanguage(),
        ]);
    }

    /**
     * @param string|null $pageTitle
     *
     * @return string
     */
    private function getPrintedPageTitle(?string $pageTitle = null): string
    {
        $printedPageTitle = $this->systemConfigurationParser->getNetworkName();
        if (null !== $pageTitle) {
            $printedPageTitle .= ' '.$pageTitle;
        }

        if ('' === trim($printedPageTitle)) {
            $printedPageTitle = 'LoxBerry '.$this->systemConfigurationParser->getLoxBerryVersion();
        }

        return $printedPageTitle;
    }

    /**
     * @param string $fileContent
     * @param array  $variables
     *
     * @return string
     */
    private function replaceVariables(string $fileContent, array $variables = []): string
    {
        foreach ($variables as $key => $value) {
            $fileContent = str_replace('<TMPL_VAR '.$key.'>', $value, $fileContent);
        }

        return $fileContent;
    }
}
