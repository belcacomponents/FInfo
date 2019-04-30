<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Belca\FInfo\Fileinfo;
use Belca\FInfo\FileExplorer;
use Belca\FInfo\BasicFileinfo;

final class BasicFileinfoTest extends TestCase
{
    protected $countMethods = 3;

    protected $countProperties = 3;

    protected $nonexistentFile = './lol';

    protected $existingFile = '/files/logo-dios.png';

    protected $basicFileinfoExtractionMethods = [
        'created' => 'getCreatedProperty',
        'edited' => 'getEditedProperty',
        'size' => 'getSizeProperty'
    ];

    protected $basicFileinfoVirtualProperties = [
        'created' => 'getCreatedProperty',
        'edited' => 'getEditedProperty',
        'size' => 'getSizeProperty',
        'filesize' => 'getSizeProperty',
    ];

    protected $basicFileinfoVirtualPropertiesData = [
        'created' => 1531556313,
        'edited' => 1531556313,
        'size' => 9399,
        'filesize' => 9399,
    ];

    protected $basicFileinfoMethodsData = [
        'getCreatedProperty' => 1531556313,
        'getEditedProperty' => 1531556313,
        'getSizeProperty' => 9399,
    ];

    protected $basicFileinfoPropertiesData = [
        'created' => 1531556313,
        'edited' => 1531556313,
        'size' => 9399,
    ];

    protected $basicFileinfo;

    /**
     * Генерация методов извлечения.
     */
    public function testGetExtractionMethods()
    {
        $this->assertEquals($this->countMethods, count(BasicFileinfo::getExtractionMethods()));
        $this->assertEquals(['created' => 'getCreatedProperty', 'edited' => 'getEditedProperty', 'size' => 'getSizeProperty'], BasicFileinfo::getExtractionMethods(), "\$canonicalize = true");
    }

    /**
     * Возвращает необработанные имена свойств класса.
     */
    public function testGetProperties()
    {
        $this->assertEquals($this->countProperties, count(BasicFileinfo::getProperties()));
    }

    /**
     * Получаем алиасы извлекаемых свойств.
     */
    public function testGetAliases()
    {
        $this->assertEquals(1, count(BasicFileinfo::getAliases()));
        $this->assertArraySubset(['filesize' => 'size'], BasicFileinfo::getAliases());
    }

    /**
     * Получаем класс по указанному методу и типу MIME.
     */
    public function testGetMimes()
    {
        // Данный класс не содержит типов, т.к. работает со всеми типами.
        $this->assertEquals(0, count(BasicFileinfo::getMimes()));
    }


    // WARNING: Noт-static methods

    /**
     * Задаем несуществующий файл для обработки.
     */
    public function testInitFailure()
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertFalse($this->basicFileinfo->setFile($this->nonexistentFile));
        $this->assertEquals(null, $this->basicFileinfo->getFile());
    }

    /**
     * Задаем существующий файл для обработки.
     */
    public function testInitTrue()
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertTrue($this->basicFileinfo->setFile(__DIR__ . $this->existingFile));
        $this->assertEquals(__DIR__ . $this->existingFile, $this->basicFileinfo->getFile());
    }

    /**
     * Получаем доступные виртуальные свойства.
     */
    public function testGetVirtualProperties()
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertEquals(4, count($this->basicFileinfo->getVirtualProperties()));
        $this->assertEquals($this->basicFileinfoVirtualProperties, $this->basicFileinfo->getVirtualProperties(), "\$canonicalize = true");
    }

    /**
     * Возвращает все значения свойства с разными вариантами ответа.
     */
    public function testGetAll()
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertTrue($this->basicFileinfo->setFile(__DIR__ . $this->existingFile));
        $this->assertEquals($this->basicFileinfoVirtualPropertiesData, $this->basicFileinfo->getAll(), "\$canonicalize = true");
        $this->assertEquals($this->basicFileinfoMethodsData, $this->basicFileinfo->getAll(BasicFileinfo::INFO_METHODS), "\$canonicalize = true");
        $this->assertEquals($this->basicFileinfoPropertiesData, $this->basicFileinfo->getAll(BasicFileinfo::INFO_PROPERTIES), "\$canonicalize = true");
    }

    /**
     * Запрос методов.
     *
     * @dataProvider methodProvider
     */
    public function testGetValueByMethod($method, $expected)
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertTrue($this->basicFileinfo->setFile(__DIR__ . $this->existingFile));
        $this->assertEquals($expected, $this->basicFileinfo->getValueByMethod($method));
    }

    public function methodProvider()
    {
        return [
            ['getSizeProperty', 9399],
            ['getCreatedProperty', 1531556313],
            ['getEditedProperty', 1531556313],
            ['getUnknownProperty', null],
            ['unknown', null],
        ];
    }

    /**
     * Запрос алиасов.
     *
     * @dataProvider aliasProvider
     */
    public function testGetValueByAlias($property, $expected)
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertTrue($this->basicFileinfo->setFile(__DIR__ . $this->existingFile));
        $this->assertEquals($expected, $this->basicFileinfo->getValueByAlias($property));
    }

    public function aliasProvider()
    {
        return [
            ['size', null],
            ['created', null],
            ['edited', null],
            ['filesize', 9399],
            ['unknown', null],
        ];
    }

    /**
     * Запрос свойств без учета алиасов.
     *
     * @dataProvider propertyProvider
     */
    public function testGetValueByProperty($property, $expected)
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertTrue($this->basicFileinfo->setFile(__DIR__ . $this->existingFile));
        $this->assertEquals($expected, $this->basicFileinfo->getValueByProperty($property));
    }

    public function propertyProvider()
    {
        return [
            ['size', 9399],
            ['created', 1531556313],
            ['edited', 1531556313],
            ['filesize', null],
            ['unknown', null],
        ];
    }

    /**
     * Запрос конкретных виртуальных свойств.
     *
     * @dataProvider virtualPropertyProvider
     */
    public function testGetValueByVirtualProperty($property, $expected)
    {
        $this->basicFileinfo = new BasicFileinfo();
        $this->assertTrue($this->basicFileinfo->setFile(__DIR__ . $this->existingFile));
        $this->assertEquals($expected, $this->basicFileinfo->getValueByVirtualProperty($property));
    }

    public function virtualPropertyProvider()
    {
        return [
            ['size', 9399],
            ['created', 1531556313],
            ['edited', 1531556313],
            ['filesize', 9399],
            ['unknown', null],
        ];
    }
}
