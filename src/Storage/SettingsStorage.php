<?php

namespace LoxBerryPoppins\Storage;

/**
 * Class SettingsStorage.
 */
class SettingsStorage
{
    const STORAGE_KEY = 'plugin-settings';

    /** @var PersistantStorage */
    private $pluginStorage;

    /** @var array|null */
    private $settings;

    /**
     * SettingsStorage constructor.
     *
     * @param PersistantStorage $pluginStorage
     */
    public function __construct(PersistantStorage $pluginStorage)
    {
        $this->pluginStorage = $pluginStorage;

        $storedSettings = $this->pluginStorage->load(self::STORAGE_KEY);
        $this->settings = $storedSettings ? json_decode($storedSettings, true) : [];
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value)
    {
        $this->settings[$key] = $value;
        $this->pluginStorage->store(self::STORAGE_KEY, json_encode($this->settings));
    }
}
