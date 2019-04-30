<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Belca\FInfo\Fileinfo;
use Belca\FInfo\FileExplorer;
use Belca\FInfo\BasicFileinfo;

final class FileinfoTest extends TestCase
{
    protected $nonexistentFile = './lol';

    protected $existingFile = '/files/logo-dios.png';

    protected $fileinfo = [
        'created' => 1531556313,
        'edited' => 1531556313,
        'size' => 9399,
        'filesize' => 9399,
    ];

    /**
     * Добавление классов в список используемых классов.
     */
    public function testAddClass()
    {
        $this->assertEquals(0, count(Fileinfo::getClasses()));

        // Добавляем новый класс
        Fileinfo::addClass(BasicFileinfo::class);
        $this->assertEquals(1, count(Fileinfo::getClasses()));
        $this->assertEquals(
            [BasicFileinfo::class],
            Fileinfo::getClasses()
        );

        // Повторно добавляем класс
        Fileinfo::addClass(BasicFileinfo::class);
        $this->assertEquals(1, count(Fileinfo::getClasses()));

        // Добавляем неподходящий класс (абстрактный класс)
        $this->assertFalse(Fileinfo::addClass(FileExplorer::class));
        $this->assertEquals(1, count(Fileinfo::getClasses()));
    }

    /**
     * Получаем список добавленных классов.
     *
     * @depends testAddClass
     */
    public function testGetClasses()
    {
        $this->assertEquals([BasicFileinfo::class], Fileinfo::getClasses());
    }

    /**
     * Получаем список добавленных классов.
     *
     * @depends testAddClass
     */
    public function testGetVirtualProperties()
    {
        $this->assertEquals(['created', 'edited', 'size', 'filesize'], Fileinfo::getVirtualProperties(), "\$canonicalize = true");

        $this->assertEquals([
            'created' => [BasicFileinfo::class => []],
            'edited' => [BasicFileinfo::class => []],
            'size' => [BasicFileinfo::class => []],
            'filesize' => [BasicFileinfo::class => []]
        ], Fileinfo::getVirtualProperties(true));
    }

    /**
     * Получаем список классов извлекающих указанное виртуальное свойство.
     *
     * @depends testAddClass
     */
    public function testGetClassesByVirtualProperty()
    {
        $this->assertEquals([BasicFileinfo::class], Fileinfo::getClassesByVirtualProperty('size'));
    }

    /**
     * Получаем список классов извлекающих указанное виртуальное свойство по
     * указанному типу файла.
     *
     * @depends testAddClass
     */
    public function testGetClassesByVirtualPropertyAndMime()
    {
        $this->assertEquals([BasicFileinfo::class => false], Fileinfo::getClassesByVirtualPropertyAndMime('size', 'image/jpeg'));
    }

    /**
     * Получаем класс извлекающий указанное виртуальное свойство.
     *
     * @depends testAddClass
     */
    public function testGetClassByVirtualProperty()
    {
        $this->assertEquals(BasicFileinfo::class, Fileinfo::getClassByVirtualProperty('size'));
        $this->assertEquals(BasicFileinfo::class, Fileinfo::getClassByVirtualProperty('size', 'image/jpeg'));
    }

    /**
     * Получаем конкретную информацию о файле.
     *
     * @depends testAddClass
     */
    public function testGetFileinfo()
    {
        $this->assertEquals(9399, Fileinfo::getFileinfo(__DIR__ . $this->existingFile, 'size'));
        $this->assertEquals(9399, Fileinfo::getFileinfo(__DIR__ . $this->existingFile, 'filesize'));
    }

    /**
     * Получаем информацию о файле.
     *
     * @depends testAddClass
     */
    public function testFile()
    {
        $this->assertEquals($this->fileinfo, Fileinfo::file(__DIR__ . $this->existingFile));

        $customFileinfo = $this->fileinfo;
        unset($customFileinfo['filesize']);
        $this->assertEquals($customFileinfo, Fileinfo::file(__DIR__ . $this->existingFile, ['created', 'edited', 'size']));

        // Указываем отсутствующее свойство
        $this->assertEquals($customFileinfo, Fileinfo::file(__DIR__ . $this->existingFile, ['created', 'edited', 'size', 'length']));
    }

    /**
     * Получаем тип Mime о файле.
     */
    public function testGetFileType()
    {
        $this->assertEquals("image/png", Fileinfo::getFileType(__DIR__ . $this->existingFile));
    }
}
