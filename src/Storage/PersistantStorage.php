<?php

namespace LoxBerryPoppins\Storage;

use LoxBerry\System\PathProvider;
use LoxBerry\System\Paths;
use LoxBerry\System\Plugin\PluginPathProvider;

/**
 * Class PersistantStorage.
 */
class PersistantStorage
{
    const STORAGE_FOLDER = 'storage';
    const STORAGE_FOLDER_INDEX = 'storage_index.json';

    /** @var PathProvider */
    private $pathProvider;

    /** @var array */
    private $index;

    /**
     * PersistantStorage constructor.
     *
     * @param PathProvider $pathProvider
     * @param $packageName
     */
    public function __construct(PluginPathProvider $pathProvider, $packageName)
    {
        $this->pathProvider = $pathProvider;
        $this->pathProvider->setPluginName($packageName);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function store(string $key, string $value)
    {
        if (!is_array($this->index)) {
            $this->loadIndex();
        }

        $this->storeIndexed($key, $value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!is_array($this->index)) {
            $this->loadIndex();
        }

        return array_key_exists($key, $this->index);
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed|null
     */
    public function load(string $key, $default = null): ?string
    {
        if (!is_array($this->index)) {
            $this->loadIndex();
        }

        if (!$this->has($key)) {
            return $default;
        }

        $storedContent = file_get_contents($this->getStorageFolder().'/'.$this->index[$key]);

        return false !== $storedContent ? $storedContent : null;
    }

    /**
     * @param string $key
     */
    public function remove(string $key)
    {
        if (!is_array($this->index)) {
            $this->loadIndex();
        }

        if ($this->has($key)) {
            $fileName = $this->getStorageFolder().'/'.$this->index[$key];
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            unset($this->index[$key]);
            $this->flushIndex();
        }
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function storeIndexed(string $key, string $value)
    {
        if (!array_key_exists($key, $this->index)) {
            do {
                $fileName = md5(uniqid(time(), true));
            } while (file_exists($this->getStorageFolder().'/'.$fileName));
            $this->index[$key] = $fileName;
        } else {
            $fileName = $this->index[$key];
        }

        file_put_contents($this->getStorageFolder().'/'.$fileName, $value);
        $this->flushIndex();
    }

    private function loadIndex()
    {
        $storageFolder = $this->getStorageFolder();
        $indexFile = $storageFolder.'/'.self::STORAGE_FOLDER_INDEX;

        if (!is_dir($storageFolder) && !mkdir($storageFolder) && !is_dir($storageFolder)) {
            throw new \RuntimeException('Could not create storage folder');
        }

        if (!file_exists($indexFile)) {
            $this->index = [];

            return;
        }

        $this->index = json_decode(file_get_contents($indexFile), true);
    }

    private function flushIndex()
    {
        $indexFile = $this->getStorageFolder().'/'.self::STORAGE_FOLDER_INDEX;
        $index = json_encode($this->index ?? []);
        file_put_contents($indexFile, $index);
    }

    /**
     * @return string
     */
    private function getStorageFolder(): string
    {
        return rtrim($this->pathProvider->getPath(Paths::PATH_PLUGIN_DATA), '/').'/'.self::STORAGE_FOLDER;
    }
}
