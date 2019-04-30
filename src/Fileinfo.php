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
    private static $classes;

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
    private static $virtualProperties;

    /**
     * Список виртуальных свойств запрашиваемых при обработке файла в случае
     * отсутствия указанных виртуальных свойств и значения переменной
     * $getAll - false.
     *
     * Пример:
     * $defaultVirtualProperties = [
     *     'size',
     * ];
     *
     * @var array
     */
    protected static $defaultVirtualProperties;

    /**
     * True означает возвращать всю возможную информацию о файле. При значении
     * true игнорируется список из массива $defaultMethods и используется
     * $virtualProperties.
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
        if (! isset(self::$classes)) {
            self::$classes = [];
        }

        // Добавление класса происходит с извлечением информации,
        // чтобы ускорить поиск необходимых классов для запуска обработки:
        // типы файлов, свойства файлов, методы классов.
        if (! in_array($className, self::$classes)) {
            if (class_exists($className)) {
                if (is_subclass_of($className, FileExplorer::class)) {

                    self::$classes[] = $className;

                    $features = static::getClassFeatures($className);

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
        return self::$classes;
    }

    /**
     * Возвращает информацию о возможностях класса: обрабатываемые типы,
     * используемые методы извлечения.
     * Если указан массив $requiredData (запрашиваемые данные класса), то
     * возвращаются только указанные и поддерживаемые данные: mimes,
     * virtual_properties, properties, aliases, methods.
     *
     * @param  string $className
     * @param  array  $requiredData
     * @return mixed
     */
    public static function getClassFeatures($className, $requiredData = ['mimes', 'virtual_properties'])
    {
        if (class_exists($className)) {
            if (is_subclass_of($className, FileExplorer::class)) {
                $data = [];

                if (isset($requiredData) && is_array($requiredData)) {
                    if (in_array('mimes', $requiredData)) {
                        $data['mimes'] = $className::getMimes();
                    }

                    if (in_array('virtual_properties', $requiredData)) {
                        $data['virtual_properties'] = array_keys($className::getVirtualProperties());
                    }

                    if (in_array('methods', $requiredData)) {
                        $data['methods'] = $className::getExtractionMethods();
                    }

                    if (in_array('properties', $requiredData)) {
                        $data['properties'] = $className::getProperties();
                    }

                    if (in_array('aliases', $requiredData)) {
                        $data['aliases'] = array_keys($className::getAliases());
                    }
                }

                return $data;
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
     * Возвращает все виртуальные свойства.
     * Если $fullDetails - true, то возвращает также классы-обработчики и
     * типы поддерживаемых файлов. В качестве ключей массива виртуальные свойства.
     *
     * @param  boolean $fullDetails
     * @return mixed
     */
    public static function getVirtualProperties($fullDetails = false)
    {
        return $fullDetails ? self::$virtualProperties : array_keys(self::$virtualProperties);
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
    public static function getClassByVirtualProperty($property, $mime = null)
    {
        if (isset(self::$virtualProperties[$property])) {

            $universalClass = null;

            foreach (self::$virtualProperties[$property] as $className => $mimes) {
                if (isset($mime) && in_array($mime, $mimes)) {
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
     * Устанавливает опцию возврата всей информации при отсутствии указанных
     * виртуальных свойств. True - возврат всей информации, false - используются
     * виртуальные поля по умолчанию.
     *
     * @param boolean $state
     */
    public static function setStateGetAll($state)
    {
        static::$getAll = $state;
    }

    /**
     * Возвращает статус "возврат всей информации о файле".
     *
     * @return boolean
     */
    public static function getStateGetAll()
    {
        return static::$getAll;
    }

    /**
     * Добавляет виртуальное свойство для обработки по умолчанию. Если указан
     * тип MIME, то обработка будет применяться для файлов с указанным типом.
     * Если $replace - true, то при добавлении такого же свойства предыдущее
     * значение будет заменено.
     *
     * @param string  $property
     * @param array   $mime
     * @param boolean $replace
     * @return void
     */
    public static function addDefaultVirtualProperty($property, $mime = [], $replace = false)
    {
        if (empty(self::$defaultVirtualProperties)) {
            self::$defaultVirtualProperties = [];
        }

        if (! isset(self::$defaultVirtualProperties[$property]) || $replace) {
            self::$defaultVirtualProperties[$property] = is_array($mime) ? $mime : [];
        } else {
            self::$defaultVirtualProperties[$property] = array_merge(self::$defaultVirtualProperties[$property], $mime);
        }
    }

    /**
     * Возвращает список виртуальных свойств вызываемых по умолчанию
     * (используется, когда не указаны запрашиваемые виртуальные свойства).
     * Если $fullDetails - true, то возвращает информацию об обрабатываемых
     * типах файлов. В качестве ключей массива виртуальные свойства.
     *
     * @param  boolean $fullDetails
     * @return array
     */
    public static function getDefaultVirtualProperties($fullDetails = false)
    {
        return $fullDetails ? self::$defaultVirtualProperties : array_keys(self::$defaultVirtualProperties);
    }

    /**
     * Сбрасываем список виртуальных полей по умолчанию.
     */
    public static function resetDefaultVirtualProperties()
    {
        self::$defaultVirtualProperties = null;
    }

    /**
     * Возвращает сведения о файле.
     *
     * Возвращаемая информация основывается на указанных виртуальных свойствах
     * и опциях обработки файлов. Если файл не существует или к нему нет доступа,
     * то возвращается значение false.
     *
     * @param  string        $filename          Полный путь к файлу
     * @param  array|string  $virtualProperties Извлекаемые свойства
     * @param  array         $options           Опции обработки файла
     * @return mixed
     */
    public static function file($filename, $virtualProperties = null/*, $options = []*/)
    {
        if (file_exists($filename)) {

            $values = [];
            $propertyValue = null;

            // Возвращаем перечисленные свойства из $virtualProperties
            if (isset($virtualProperties)) {

                // Извлекаем одно свойство
                if (is_string($virtualProperties) && isset(self::$virtualProperties[$virtualProperties])) {
                    $propertyValue = static::getFileinfo($filename, $virtualProperties/*, $options*/);

                    return isset($propertyValue) ? [$virtualProperties => $propertyValue] : [];
                }
                // Извлекаем перечисленные свойства или все свойства, если задан пустой массив
                elseif (is_array($virtualProperties)) {

                    // При пустом массиве извлекаем все свойства
                    if (count($virtualProperties) == 0) {
                        $virtualProperties = static::getVirtualProperties();
                    }
                }
            }
            // Возвращаем всю информацию о файле
            elseif (self::$getAll || empty(self::$defaultVirtualProperties)) {
                $virtualProperties = static::getVirtualProperties();
            }
            // Возвращает свойства по умолчанию
            else {
                $virtualProperties = static::getDefaultVirtualProperties();
            }

            if (isset($virtualProperties) && is_array($virtualProperties) && count($virtualProperties)) {
                foreach ($virtualProperties as $property) {
                    $propertyValue = static::getFileinfo($filename, $property/*, $options*/);

                    if (isset($propertyValue)) {
                        $values[$property] = $propertyValue;
                    }
                }
            }

            return $values;
        }

        return false;
    }

    /**
     * Возвращает информацию о файле по указанному методу.
     *
     * При недоступности файла возвращает false. При отсутствии указанного
     * свойства возвращается null.
     *
     * @param  string $filename
     * @param  string $virtualProperty
     * @param  array  $options
     * @return mixed
     */
    public static function getFileinfo($filename, $virtualProperty/*, $options = []*/)
    {
        if (file_exists($filename)) {
            if (is_string($virtualProperty) && isset(self::$virtualProperties[$virtualProperty])) {

                $className = self::getClassByVirtualProperty($virtualProperty, static::getFileType($filename));

                $class = new $className(); // TODO постоянно создаются существующие классы
                $class->setFile($filename); // TODO постоянно задается одинаковый файл для обработки

                return $class->getValueByVirtualProperty($virtualProperty);
            }

            return null;
        }

        return false;
    }

    /**
     * Возвращает MIME-тип файла, если файл существует. Иначе возвращает null.
     *
     * @param  string $filename
     * @return string
     */
    public static function getFileType($filename)
    {
        return file_exists($filename) ? mime_content_type($filename) : null;
    }
}
