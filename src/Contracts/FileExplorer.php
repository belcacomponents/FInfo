<?php

abstract class FileExplorer
{
    /**
     * Список поддерживаемых типов.
     *
     * Если не заполнено, то считается, что класс может получить сведения о
     * любом файле, если иное не определено при проверке файла.
     *
     * @var array
     */
    protected static $mimes = [];

    /**
     * Список алиасов методов.
     *
     * Содержит название алиаса и выполняемую функцию.
     *
     * @var array
     */
    protected static $aliases = [];

    /**
     * Методы класса для получения информации о файле.
     *
     * @var array
     */
    protected static $methods = [];

    /**
     * Путь к обрабатываемому файлу.
     *
     * @var string
     */
    protected $filename;

    public function __construct($filename = null)
    {
        self::$methods = self::getClassMethods();

        if ($filename) {
            $this->setFile($filename);
        }
    }

    /**
     * Возвращает имена методов реализуемого класса для получения данных.
     *
     * @var mixed
     */
    protected static getClassMethods()
    {
        // TODO определяет список методов класса со значениями get Name Value
        return [];
    }

    /**
     * Возвращает список методов обработки файла.
     *
     * @return array
     */
    public static function getMethods()
    {
        return self::$methods;
    }

    /**
     * Возвращает алиасы класса.
     *
     * @return array
     */
    public static function getAliases()
    {
        return self::$aliases;
    }

    /**
     * Возвращает список поддерживаемых Mime-типов.
     *
     * @return array
     */
    public static function getMimes()
    {
        return self::$mimes;
    }

    /**
     * Устанавливает файл для обработки.
     *
     * @return bool
     */
    public function setFile($filename)
    {
        if (file_exists($filename)) {
            $this->filename = $filename;

            return true;
        }

        return false;
    }

    /**
     * Выполняет проверку на совместимость работы с файлом.
     *
     * Если работа с файлам (получение информации о файле) невозможна,
     * то возвращается false.
     *
     * @var boolean
     */
    abstract public function checkCompatibility();

    /**
     * Возвращает всю информацию о файле.
     *
     * @param bool $aliases  Если true, то возвращает продублированную информацию
     * с алиасами TODO возможно лучше константы и 3 значения: дублировать, только функции, только константы
     * @return mixed
     */
    public function getAll($aliases = true)
    {
        // TODO вызов всех методов из массива
        // каждому значению соответствует название метода и/или алиаса
    }

    /**
     * Возвращает значение по указанному методу.
     * @param  [type] $method [description]
     * @return [type]         [description]
     */
    public function getValueByMethod($method) // getValueByName($name)
    {
        // из статического списка значений проверяем наличие (в т.ч. в алиасах)
        // и вызываем функцию
    }
}
