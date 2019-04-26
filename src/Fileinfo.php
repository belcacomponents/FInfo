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
     * Методы обработки данных и обрабатываемые типы.
     *
     * Пример:
     * $methods = [
     *     'tool' => ['\Belca\FInfoTools'],
     *     'colors' => [
     *         '\Belca\FInfoColors' => ['image/jpeg', 'image/pjpeg'],
     *         '\Belca\FInfoMixedColors' => ['image/jpeg'],
     *     ],
     *
     * ]
     *
     * @var mixed
     */
    protected static $methods = [];

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
     * Добавляет класс в список обработчиков.
     *
     * При успешном добавлении возвращает true.
     *
     * @param string $className Имя класса-обработчика
     * @return bool
     */
    public static function addClass($className)
    {
        /**
         * Выполняется проверка добавляемого класса в массиве, проверяется
         * существование класса и его принадлежность интерфейсу FileExplorer.
         *
         * В случае успеха, класс добавляется в список и из него извлекается
         * информация о методах и обрабатываемых типов данных.
         */
        if (! in_array($className, self::$classes)) {
            if (class_exists($className)) {
                if (is_subclass_of($className, FileExplorer::class)) {

                    self::$classes[] = $className;

                    $features = self::getClassFeatures($className);

                    /**
                     * Добавляем полученные методы и типы в массив возможных
                     * обработок.
                     */
                    if ($features) {
                        foreach ($features['methods'] as $method) {
                            self::addMethod($method, $features['mimes']);
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
        return self::$classes;
    }

    /**
     * Возвращает список классов соответствующих указанному методу обработки.
     *
     * @param  string $method Метод обработки или свойство
     * @return array
     */
    public static function getClassesByMethod($method)
    {
        return self::$methods;
    }

    /**
     * Возвращает первый подходящий класс для обработки указанного метода и типа
     * файла.
     *
     * В случае отсутствия такого класса, возвращает false.
     * Возможно возвращение "уникального" класса, в котором не были указаны
     * обрабатываемые типы, но это не всегда значит, что он может получить
     * сведения о файле.
     *
     * @param  string $method Метод обработки или свойство
     * @param  string $mime   Тип MIME
     * @return string|boolean
     */
    public static function getClassByMethodAndMime($method, $mime)
    {
        // проверяет все классы
        // сначала с типами, затем универсальные, т.е. вначале проходит разделение,
        // хотя можно просто найти 2 класса
    }

    /**
     * Возвращает список классов подходящие для получения указанного свойства
     * и обработки указанного типа файла.
     *
     * @param  string $method Метод обработки
     * @param  string $mime   Тип MIME
     * @param  bool   $type   Если указано true, то возвращает двумерный массив,
     * где значением является конкретность (true) или универсальность (false)
     * обработки.
     * @return mixed
     */
    public static function getClassesByMethodAndMime($method, $mime, $type = false)
    {
        // проверяет все классы
        // может возвращать двумерный массив со значением конкретной обработки
        // или универсальности.
    }

    /**
     * Возвращает информацию о возможностях класса: обрабатываемые типы,
     * используемые методы, алиасы.
     *
     * @param  string $className Имя файла
     * @return mixed
     */
    public static function getClassFeatures($className)
    {
        if (class_exists($className)) {
            if (is_subclass_of($className, FileExplorer::class)) {
                return [
                    'mimes' => $className::getMimes(),
                    'methods' => $className::getMethods(),
                    'aliases' => $className::getAliases(),
                ];
            }
        }

        return false;
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
