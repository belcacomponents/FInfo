<?php

namespace Belca\FInfo;

use Belca\FInfo\FileExplorer;

class Fileinfo
{
  // TODO здесь
  // принимается файл
  // применятся настройки - обработчики
  // обрабатываются заданные классы
  // продумать расширение и подмену

    /**
     * Классы содержащие методы получения информации.
     *
     * Все классы должны реализовывать интерфейс \BelcaContracts\FileExplorer
     *
     * Пример:
     * $classes = [
     *     '\Belca\FInfo\BasicFileinfo',
     *     '\Belca\FInfoImage\JPGFileinfo',
     *     '\Belca\FInfoImage\PNGFileinfo',
     * ];
     *
     * @var array
     */
    protected static $classes = [];

    /**
     * Виртуальные свойства и обрабатываемые типы.
     *
     * Пример:
     * $methods = [
     *     'tool' => ['\Belca\FInfoTools' => []],
     *     'colors' => [
     *         '\Belca\FInfoColors' => ['image/jpeg', 'image/pjpeg'],
     *         '\Belca\FInfoMixedColors' => ['image/jpeg'],
     *     ],
     *
     * ]
     *
     * @var mixed
     */
    protected static $virtualProperties;

    /**
     * Список методов применяемый при обработке файла в случае отсутствия
     * указанных методов и значения переменной $getAll - false.
     *
     * Пример:
     * $defaultMethods = [
     *     'size',
     * ];
     *
     * @var array
     */
    protected static $defaultMethods = [];

    /**
     * True означает возвращать всю возможную информацию о файле. При значении
     * true игнорируется список из массива $defaultMethods и используется
     * $methods.
     *
     * @var bool
     */
    protected static $getAll = false;

    /**
     * Добавляет класс расширенный от FileExplorer в список обработчиков файла.
     * При успешном добавлении возвращает true.
     *
     * @param  string $className
     * @return bool
     */
    public static function addClass($className)
    {
         // Выполняется проверка добавляемого класса в массиве, проверяется
         // существование класса и его принадлежность абстрактному FileExplorer.
         // В случае успеха, класс добавляется в список и из него извлекается
         // информация о методах и обрабатываемых типах данных.
         //
         // Добавление класса происходит по одному, чтобы собрать всю информацию
         // об обрабатываемых данных: типы файлов, свойства файлов, методы
         // классов.
        if (! in_array($className, static::$classes)) {
            if (class_exists($className)) {
                if (is_subclass_of($className, FileExplorer::class)) {

                    static::$classes[] = $className;

                    $features = static::getClassFeatures($className);

                    // Должен быть инициализирован только класс унаследованный
                    // от FileExplorer
                    if (isset($features)) {
                        foreach ($features['virtual_properties'] as $property) {
                            static::addVirtualProperty($property, $className, $features['mimes']);
                        }
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Возвращает список объявленных классов.
     *
     * @return array
     */
    public static function getClasses()
    {
        return static::$classes;
    }

    /**
     * Возвращает список классов подходящих для извлечения указанного свойства
     * из указанного типа файла. Если класс-обработчик обрабатывает все типы,
     * то он также будет возвращен.
     * В результате, в качестве ключей используются имена классов, а в качестве
     * значения true или false. True - поддерживается указанный тип, false -
     * универсальный класс-обработчик и обрабатывает все файлы, если иное не
     * определено при обработке файла.
     *
     * @param  string $property
     * @param  string $mime
     * @return array
     */
    public static function getClassesByVirtualPropertyAndMime($property, $mime)
    {
        if (isset(self::$virtualProperties[$property])) {

            $matches = [];

            foreach (self::$virtualProperties[$property] as $className => $mimes) {
                if (isset($mimes) && in_array($mime, $mimes)) {
                    $matches[$className] = true;
                } elseif (empty($mimes)) {
                    $matches[$className] = false;
                }
            }

            return $matches;
        }

        return [];
    }

    /**
     * Возвращает список классов извлекающих указанное виртуальное свойство без
     * учета типа файла.
     * Если $fullDetails - true, то будет возвращена вся информация классов
     * об обрабатываемых типах файлов, иначе - просто список классов.
     *
     * @param  string  $property
     * @param  boolean $fullDetails
     * @return mixed
     */
    public static function getClassesByVirtualProperty($property, $fullDetails = false)
    {
        if (isset(self::$virtualProperties[$property])) {
            return $fullDetails ? self::$virtualProperties[$property] : array_keys(self::$virtualProperties[$property]);
        }

        return [];
    }

    /**
     * Возвращает первый наиболее подходящий класс для извлечения указанного
     * виртуального свойства и типа файла.
     * Если подходящий класс не найден - вернет null.
     *
     * @param  string $property
     * @param  string $mime
     * @return string|null
     */
    public static function getClassByVirtualProperty($property, $mime)
    {
        if (isset(self::$virtualProperties[$property])) {

            $universalClass = null;

            foreach (self::$virtualProperties[$property] as $className => $mimes) {
                if (isset($mimes) && in_array($mime, $mimes)) {
                    return $className;
                } elseif (empty($universalClass) && empty($mimes)) {
                    $universalClass = $className;
                }
            }

            return $universalClass;
        }

        return null;
    }

    /**
     * Возвращает информацию о возможностях класса: обрабатываемые типы,
     * используемые методы извлечения.
     *
     * @param  string $className
     * @return mixed
     */
    public static function getClassFeatures($className)
    {
        if (class_exists($className)) {
            if (is_subclass_of($className, FileExplorer::class)) {
                return [
                    'mimes' => $className::getMimes(),
                    //'methods' => $className::getExtractionMethods(), // могут пригодиться при вызове конкретного класса и метода, хотя можно проверить при вызове
                    'virtual_properties' => $className::getVirtualProperties(),
                ];
            }
        }

        return false;
    }

    /**
     * Добавляет новое или обновляет возможности имеющегося виртуального
     * свойства в список поддерживаемых виртуальных свойств. Если
     * список поддерживаемых типов пуст, то считается, что класс-обработчик
     * работает со всеми типами, если иное не определено в при обработке
     * в классе-обработчике.
     *
     * @param string $property Кодовое название виртуального свойства
     * @param string $class    Класс-обработчик
     * @param array  $mimes    Поддерживаемые типы
     */
    protected static function addVirtualProperty($property, $class, $mimes = [])
    {
        if (empty(self::$virtualProperties)) {
            self::$virtualProperties = [];
        }

        if (empty(self::$virtualProperties[$property])) {
            self::$virtualProperties[$property] = [];
        }

        self::$virtualProperties[$property][$class] = $mimes;
    }

    /**
     * Возвращает все виртуальные свойства, обрабатываемые классы и типы файлов.
     *
     * @return mixed
     */
    public static function getVirtualProperties()
    {
        return self::$virtualProperties;
    }

    protected static function addMethod($method, $class)
    {
        // добавляет метод и класс обработчик.
        // если метод существует и хранимые значения не массив - сделать массив,
        // перенести существующее значение, добавить новое.
    }

    /**
     * Возвращает список методов обработки.
     *
     * @param boolean $classes  Возвращать классы методов. По умолчанию false.
     * @return array
     */
    public static function getMethods($classes = false)
    {
        return self::$methods; // TODO только ключи
    }

    /**
     * Возвращает список методов обработки по умолчанию.
     *
     * @return array
     */
    public static function getDefaultMethods()
    {
        return self::$defaultMethods;
    }

    /**
     * Добавить метод обработки по умолчанию.
     *
     * @param string  $method Метод обработки
     * @param boolean $exists Существование метода в списке методов
     */
    public static function addDefaultMethod($method, $exists = false)
    {
        // проверяет его наличие и добавляем, чтобы не было дубликатов
    }

    /**
     * Возвращает сведения о файле.
     *
     * Возвращаемая информация основывается на указанных методах, опциях,
     * классах-обработчиках. Если файл не существует или к нему нет доступа,
     * то возвращается значение false.
     *
     * @param  string                $filename Путь к файлу
     * @param  array|string|integer  $methods  Методы извлечения данных или виды требуемой информации
     * @param  array                 $options  Опции обработки файла
     * @return mixed
     */
    public static function file($filename, $methods = [], $options = [])
    {
        if (file_exists($filename)) {
            if (isset($methods)) {
                // TODO если методы все-таки заданы, то проверить тип значения
                // и продолжить разработку

                return [];
            }

            if (self::$getAll) {
                // вернуть всю информацию о файлах
                $methods = self::getMethods();
                // циклом пойти извлекать значения
                // TODO вернуть массив свойств
            } else {
                // вернуть только важную информацию о файлах
                $methods = self::getDefaultMethods();
                // вернуть массив свойств
            }
        }

        return false;
    }

    /**
     * Возвращает информацию о файле по указанному методу.
     *
     * При недоступности файла возвращает false.
     *
     * @param  string $filename Путь к файлу
     * @param  string $method   Название метода извлечения информации или свойство
     * @param  array  $options  Опции получения информации
     * @return mixed
     */
    public static function getFileinfo($filename, $method, $options = [])
    {
        if (file_exists($filename)) {
            if (is_string($method) && isset(self::$methods[$method])) {

                $mime = mime_type($filename);

                $className = self::getClassByMethodAndMime($method, $mime);

                // Вызываем указанный метод получения информации или свойство
                //$className->getValueByName($method)
            }
        }

        return false;
    }
}
