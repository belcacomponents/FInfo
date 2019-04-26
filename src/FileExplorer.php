<?php

namespace Belca\FInfo;

/**
 * Абстрактный класс для реализации классов извлекающего или генерирующего значения
 * свойств файла.
 */
abstract class FileExplorer
{
    const INFO_VIRTUAL_PROPERTIES = 0;
    const INFO_METHODS = 1;
    const INFO_PROPERTIES = 2;

    /**
     * Список поддерживаемых типов файлов.
     *
     * Если не заполнено, то считается, что класс может получить сведения о
     * любом файле, если иное не определено при проверке файла.
     *
     * @var array
     */
    protected static $mimes;

    /**
     * Методы извлечения виртуальных свойств.
     *
     * Определяется автоматически при вызове метода getMethods()
     *
     * @var array
     */
    private static $extractionMethods;

    /**
     * Список алиасов (синонимов) виртуальных свойств.
     *
     * Содержит название алиаса и название оригинального свойства (метода
     * извлечения).
     *
     * Пример:
     * $aliases = [
     *     'color' => 'colorForHuman', // ссылается на виртуальное свойство
     *     'humanColor' => 'getHumanColor', // ссылается на метод извлечения
     * ];
     *
     * @var array
     */
    protected static $aliases = [];

    /**
     * Список алиасов виртуальных свойств в качестве ключей и вызываемых
     * методов в качестве значений.
     * Данное значение полность или частично дублирует переменную $aliases.
     * В отличие от $aliases здесь хранятся только вызываемые методы извлечения,
     * если они существуют.
     *
     * @var array
     */
    private static $aliasMethods;

    /**
     * Сокращенные имена методов класса (виртуальных свойств) и методов
     * извлечения для получения информации о файле.
     * В качестве ключей идут имена методов извлечения, а в качестве значений -
     * виртуальные свойства.
     *
     * Пример:
     * $properties = [
     *     'getColorForHumanValue' => 'colorForHuman',
     *     'getPrimaryColorsValue' => 'primaryColors',
     * ];
     *
     * @var array
     */
    protected static $properties;

    /**
     * Список виртуальных свойств класса (заполняется автоматически). В качестве
     * ключей выступают названия виртуальных свойств, а в качестве значений -
     * методы извлечения.
     *
     * Список виртуальных свойств включает свойства определенных с помощью
     * названий методов извлечения или заданных вручную и алиасов свойств.
     *
     *
     * @var array
     */
    private static $virtualProperties;

    /**
     * Абсолютный путь к обрабатываемому файлу.
     *
     * @var string
     */
    protected $file;

    /**
     * Префикс имен функций для обращения к виртуальным свойствам.
     *
     * @var string
     */
    protected static $prefixFunctionName = 'get';

    /**
     * Суффикс имен функций для обращения к виртуальным свойствам.
     *
     * @var string
     */
    protected static $suffixFunctionName = 'Property';

    /**
     * Список исключенных методов.
     *
     * @var array
     */
    protected static $exceptions = ['getValueByProperty', 'getValueByVirtualProperty'];

    /**
     * Все исключенные методы, в т.ч. объявленные в новых классах.
     *
     * @var array
     */
    private static $allExpections;

    /**
     * Автозагрузка названий виртуальных свойств.
     *
     * Когда значение установлено true, каждый раз при вызове виртуальных
     * свойств генерируется список виртуальный свойств - $properties.
     *
     * @var bool
     */
    protected static $autoloadProperties = true;

    /**
     * Определяет и возвращает список методов извлечения виртуальных свойств
     * класса без учета алиасов. Список методов предоставляется в виде
     * "имя свойства" => "метод извлечения". Например, ['size' => 'getSizeProperty'].
     *
     * @return array
     */
    public static function getExtractionMethods()
    {
        if (! isset(self::$extractionMethods)) {
            $classMethods = get_class_methods(static::class);
            self::$extractionMethods = [];

            $prefixLength = strlen(static::$prefixFunctionName);
            $suffixLength = strlen(static::$suffixFunctionName);
            $subtractedLength = $prefixLength + $suffixLength;

            // Получаем имена виртуальных свойств и методов извлечения: size => getSizeProperty
            foreach ($classMethods as $method) {
                if (! static::isException($method) && static::isAppropriateMethod($method)) {
                    $property = lcfirst(substr($method, $prefixLength, strlen($method) - $subtractedLength));
                    self::$extractionMethods[$property] = $method;
                }
            }
        }

        return self::$extractionMethods;
    }

    /**
     * Проверяет, соответствует ли переданное имя метода класса имени для
     * извлечения свойств.
     *
     * @param  string  $method
     * @return boolean
     */
    protected static function isAppropriateMethod($method)
    {
        return ((substr($method, 0, strlen(static::$prefixFunctionName)) == static::$prefixFunctionName)
                && (substr($method, (-1) * strlen(static::$suffixFunctionName)) == static::$suffixFunctionName));
    }

    protected static function isException($method)
    {
        if (! isset(self::$allExpections)) {
            self::$allExpections = array_merge(self::$exceptions, static::$exceptions ?? []);
        }

        return in_array($method, self::$allExpections);
    }

    /**
     * Возвращает список виртуальных свойств полученных на основе методов
     * извлечения или заданных вручную (зависит от настроек класса).
     * В качестве ключа виртуального свойства выступает метод извлечения,
     * а в качестве значения - виртуальное свойство.
     *
     * @return array
     */
    public static function getProperties()
    {
        // Если задана автозагрузка методов извлечения свойств, то генерируем их
        if (static::$autoloadProperties) {
            if (! isset(static::$properties)) {

                // Меняем местами методы извлечения и виртуальные свойства местами
                static::$properties = array_flip(static::getExtractionMethods());
            }
        }

        return static::$properties;
    }

    /**
     * Возвращает алиасы виртуальных свойств.
     *
     * @return array
     */
    public static function getAliases()
    {
        return static::$aliases;
    }

    /**
     * Возвращает алиасы виртуальных свойств.
     *
     * @return array
     */
    public static function getAliasExtractionMethods()
    {
        if (! isset(self::$aliasMethods)) {
            $properties = static::getProperties();
            $extractionMethods = static::getExtractionMethods();
            self::$aliasMethods = [];

            // Для каждого алиаса ищем определенный метод извлечения или виртуальное
            // свойство
            foreach (static::getAliases() as $alias => $value) {
                $property = array_search($value, $properties);

                if ($property) {
                    self::$aliasMethods[$alias] = $property;
                } elseif (in_array($value, $extractionMethods)) {
                    self::$aliasMethods[$alias] = $value;
                }
            }
        }

        return self::$aliasMethods;
    }

    /**
     * Возвращает имена виртуальных свойств и методы извлечения. В качестве
     * ключей идут виртуальные свойства, а в качестве значений - методы
     * извлечения.
     *
     * В именах виртуальных свойств присутствуют алиасы и сокращенные имена
     * методов извлечения (виртуальные свойства).
     *
     * @return array
     */
    public static function getVirtualProperties()
    {
        if (! isset(self::$virtualProperties)) {
            self::$virtualProperties = array_merge(array_flip(static::getProperties()), static::getAliasExtractionMethods());
        }

        return self::$virtualProperties;
    }

    /**
     * Возвращает список поддерживаемых Mime-типов.
     *
     * @return array
     */
    public static function getMimes()
    {
        return static::$mimes ?? [];
    }

    /**
     * Устанавливает абсолютный путь к обрабатываемому файлу. Если файл
     * недоступен, то имя файла не будет присвоено и вернется false.
     *
     * @return bool
     */
    public function setFile($file)
    {
        if (file_exists($file)) {
            $this->file = $file;

            return true;
        }

        return false;
    }

    /**
     * Возвращает абсолютный путь к указанному файлу.
     *
     * @return string|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Выполняет проверку на совместимость работы с файлом.
     *
     * Если работа с файлом (получение информации о файле) невозможна,
     * то возвращается false.
     *
     * @return boolean
     */
    abstract public function checkCompatibility();

    /**
     * Возвращает всю информацию о файле.
     *
     * @param  bool   $type Тип возвращаемой информации (константы 'INFO_*')
     * @return mixed
     */
    public function getAll($type = self::INFO_VIRTUAL_PROPERTIES)
    {
        if ($type == self::INFO_METHODS) {
            // либо название метода и значение
        } elseif ($type = self::INFO_PROPERTIES) {
            // Либо вернет название свойства - значение
        } else {
            // либо название свойств и алиасов и значение
        }
    }

    /**
     * Возвращает значение свойства файла по указанному методу извлечения.
     * Если указанный метод не найден, то возвращает null.
     *
     * @param  string $method
     * @return mixed
     */
    public function getValueByMethod($name)
    {
        if (in_array($name, $this->getExtractionMethods())) {
            return $this->$name();
        }

        return null;
    }

    /**
     * Возвращает значение свойства файла по названию алиаса.
     *
     * @param  string $name
     * @return mixed
     */
    public function getValueByAlias($name)
    {
        $aliases = static::getAliasExtractionMethods();

        if (array_key_exists($name, $aliases)) {
            return $this->$aliases[$name]();
        }

        return null;
    }

    /**
     * Возвращает значение свойства файла по названию свойства определенного
     * по названию метода извлечения (например, size => getSizeProperty).
     *
     * @param  string $name
     * @return mixed
     */
    public function getValueByProperty($name)
    {
        $properties = array_flip(static::getProperties());

        if (array_key_exists($name, $properties)) {
            return $this->$properties[$name]();
        }

        return null;
    }

    /**
     * Возвращает значение свойства файла по названию виртуального свойства.
     *
     * При поиске значения свойства учитываются алиасы и свойства определенные
     * на основе имен методов.
     *
     * @param  string $name
     * @return mixed
     */
    public function getValueByVirtualProperty($name)
    {
        $properties = static::getVirtualProperties();

        if (array_key_exists($name, $properties)) {
            return $this->$properties[$name]();
        }

        return null;
    }
}
