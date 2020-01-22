<?php

namespace LoxBerryPoppins\Frontend\Twig\Extensions;

use LoxBerry\System\Localization\LanguageFileParser;
use LoxBerry\System\Localization\TranslationProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class Translations.
 */
class Translations extends AbstractExtension
{
    /** @var LanguageFileParser */
    private $systemTranslations;

    /** @var LanguageFileParser */
    private $pluginTranslations;

    /** @var LanguageFileParser */
    private $fallbackSystemTranslations;

    /** @var LanguageFileParser */
    private $fallbackPluginTranslations;

    /**
     * Translations constructor.
     *
     * @param TranslationProvider $translationProvider
     * @param $packageName
     */
    public function __construct(TranslationProvider $translationProvider, $packageName)
    {
        $fallbackLanguage = TranslationProvider::FALLBACK_LANGUAGE;

        $this->systemTranslations = $translationProvider->getSystemTranslations();
        $this->fallbackSystemTranslations = $translationProvider->getSystemTranslations(null, $fallbackLanguage);
        $this->pluginTranslations = $translationProvider->getPluginTranslations($packageName);
        $this->fallbackPluginTranslations = $translationProvider->getPluginTranslations($packageName, null, $fallbackLanguage);
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('trans', [$this, 'translate']),
            new TwigFunction('translate', [$this, 'translate']),
        ];
    }

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('trans', [$this, 'translate']),
            new TwigFilter('translate', [$this, 'translate']),
        ];
    }

    /**
     * @param string $stringToTranslate
     *
     * @return string
     */
    public function translate(string $stringToTranslate): string
    {
        foreach ([
            $this->pluginTranslations,
            $this->fallbackPluginTranslations,
            $this->systemTranslations,
            $this->fallbackSystemTranslations,
        ] as $translations) {
            $translated = $translations->getTranslated($stringToTranslate);
            if ($translated !== $stringToTranslate) {
                return $translated;
            }
        }

        return $stringToTranslate;
    }
}
