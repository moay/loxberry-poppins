<?php

namespace Tests\Storage;

use LoxBerry\System\Plugin\PluginPathProvider;
use LoxBerryPoppins\Storage\PersistantStorage;
use LoxBerryPoppins\Storage\SettingsStorage;
use PHPUnit\Framework\TestCase;

/**
 * Class StorageTest.
 */
class StorageTest extends TestCase
{
    /** @var PersistantStorage */
    private $storage;

    protected function setUp(): void
    {
        $this->wipeTestStorage();
        $pathProviderMock = $this->createMock(PluginPathProvider::class);
        $pathProviderMock
            ->method('getPath')
            ->willReturn(__DIR__);

        $this->storage = new PersistantStorage($pathProviderMock, 'test');
        $initialSettings = json_encode(['testkey' => 'testvalue']);
        $this->storage->store(SettingsStorage::STORAGE_KEY, $initialSettings);
    }

    public function testStorageIsInitializedProperly()
    {
        $this->assertFileExists(__DIR__.'/storage/'.PersistantStorage::STORAGE_FOLDER_INDEX);
    }

    public function testSettingsCanBeObtained()
    {
        $settings = new SettingsStorage($this->storage);
        $this->assertTrue($settings->has('testkey'));
        $this->assertEquals('testvalue', $settings->get('testkey'));
    }

    public function testSettingsCanBeWritten()
    {
        $settings = new SettingsStorage($this->storage);
        $this->assertFalse($settings->has('testkey2'));
        $this->assertEquals(null, $settings->get('testkey2'));
        $settings->set('testkey2', 'testvalue2');
        $this->assertEquals('testvalue2', $settings->get('testkey2'));
        $this->assertTrue($settings->has('testkey2'));
    }

    public function testDefaultIsReturnedProperly()
    {
        $settings = new SettingsStorage($this->storage);
        $this->assertEquals('dummy', $settings->get('testkey2', 'dummy'));
    }

    public function testStorageIsProperlyWritten()
    {
        $settings = new SettingsStorage($this->storage);
        $settings->set('testkey3', 'testvalue3');
        $pathProviderMock = $this->createMock(PluginPathProvider::class);
        $pathProviderMock
            ->method('getPath')
            ->willReturn(__DIR__);

        $secondStorage = new PersistantStorage($pathProviderMock, 'test');
        $secondSettings = new SettingsStorage($secondStorage);
        $this->assertEquals('testvalue3', $secondSettings->get('testkey3'));
    }

    protected function tearDown(): void
    {
        $this->wipeTestStorage();
    }

    private function wipeTestStorage()
    {
        // Removes test storage
        shell_exec('cd '.(__DIR__).' && rm -rf storage');
    }
}
