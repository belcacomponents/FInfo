# FInfo

Проблемы и решение.

Расширяемость и единый интерфейс.

Пакет FInfo (Fileinfo) разработан для Dios CMS, но может использоваться в
любом PHP проекте.

# Примеры использования

Fileinfo::file(string $filename, mixed $methods = [], mixed $options = [])

$filename - абсолютный путь к файлу.

$methods - методы получения информации о файле. Содержит одно или массив значений необходимых данных. В качестве значений выступают константы, имена методов (абстрактоного типа информации), имена классов с именем метода, имя типа с именем метода.

$options - опции получения информации о файле. В зависимости от реализации получения данных,
они могут быть использованы или проигнорированы. В зависимости от типа значений, опции могут
задаваться конкретному классу/методу или передаваться всем обработчикам.

# Виртуальные свойства и значения

Для получения конкретной информации о файле используются виртуальные свойства и значения.

Виртуальные свойства и значения (они же вычисленные свойства и высчитанные значения) - понятия введенные в этом пакете для определения получаемых значений и свойств файла. Виртуальное значение может быть получено из существующих данных файла (например, размер файла, дата изменения файла, владелец файла и т.п.) или определено на основе содержимого файла.

На самом деле, виртуальное свойство - название метода класса для получения значения свойства файла измеряемое одним значением или набором значений (массивом), а виртуальное значение - рассчитанное или извлеченное значение на основе данных файла и возвращаемое определенным методом (виртуальным свойством).

## Примеры виртуальных значений

Примером виртуальных свойств будут:

- средний цвет изображения для человека;
- основные цвета изображения;
- создатель файла (изображения, аудиотрека, видеофайла и т.п.);
- координаты места создания фотографии;
- размер файла;
- длительность файла (аудиофайла, видеофайла, анимации);
- дата создания файла и т.д.

Как можно заменить, такие значения как размер файла, геолокация, создатель файла и т.п. могут быть определены в метаданных файла или в сведениях самого файла, а значение свойств как средний цвет изображения, основные цвета изображения и т.п., отсутствуют у файлов и для их получения необходимо обработать файл с помощью специальных инструментов (библиотек, модулей, классов и т.п.).

## Расширение класса и особенности одинаковых свойств

Одинаковые имена методов могут присутствовать у обработчиков разных типов файлов и
у одинаковых типов файлов.

Например, image.jpg, image.png, image.gif, image.bmp могут иметь функцию определения цвета.
В принципе, если это возможно, то и file.pdf может иметь такую реализацию.
Для каждого типа файла будет вызван соответствующий класс обработчика и могут быть к нему
переданны дополнительные параметры.

Как работает функция?

Передается путь к файлу, методы и опции. С помощью внутренее реализации, определяется
обработчик файла, если не указано другое.
Обработчик файла определяется исходя из: наличия функции-обработчика, типа файла, возможности его обработки.

// Массив обработчиков и вызываемых функций

'tool' => \Belca\FInfoTools::class, - общий инструмент для всех типов файлов
'colors' => [
  \Belca\FInfoColors::class,
  \Belca\FInfoMixedColors::class
],
'time' => [
  'audio' => [ // для аудиофайлов
    \Belca\FInfoMP3::class, // для файлов с mime mp3
    \Belca\FInfoWAV::class, // для файлов с mime wav
    \Belca\FInfoOGG::class, // для файлов с mime ogg
  ],
  'video' => [ // для видеофайлов
    'avi' => [ // для файлов AVI
      \Belca\FInfoAVI::class, // для файлов с mime avi
      \Belca\FInfoMSAVI::class, // для файлов с mime msavi
    ],
    'video/x-matroska' => \Belca\FInfoMatroska::class, // для файлов с mime matroska
  ],
  'gif' => \Belca\FInfoGifTime::class, // для файлов с расширением gif
  'presentation' => \Belca\FInfoPPT::class, // для презентаций, в частности будет обработан только PPT
],

// Константы - содержат имена вызываемых методов с указанием класса, типа или без него
const IMAGE_HUMAN_COLOR = 'color'



Fileinfo::file('/path/to/filename') - вернет всю или основную информацию
Fileinfo::file('/path/to/filename', IMAGE_HUMAN_COLOR) - вернет информацию о среднем цвете изображения для человека, если файл является поддерживаемым изображением
Fileinfo::file('/path/to/filename', [IMAGE_HUMAN_COLOR, IMAGE_COLORS, IMAGE_ORINTATION, 'tool', 'geo']) - вернет массив с ключами запроса со значениями: средний цвет для человека, частовстречаемые цвета изображения, ориентация изображения, фотокамера или инструмент
Fileinfo::file('/path/to/filename', ['ImageInfo::color']) - получает информацию о файле, с помощью конкретный файл и метод. Данный подход не желателен, но возможен, когда одинаковые методы используются в разных классах и дают разный результат

Fileinfo::getClasses() - возвращает список используемых классов
Fileinfo::getClassByMethodAndMime('tool', 'mime/type') - возвращает первый подходящий класс для извлечения данных о файле по указанному методу и типу
/*Fileinfo::getClassByConstant(CONSTANT) - возвращает первый найденный класс для извлечения данных по указанной константе*/
Fileinfo::getClassesByMethod('colors') - возвращает список классов обрабатывающих данный метод
Fileinfo::getClassesByMethod('colors', 'mime/type') - возвращает список классов обрабатывающих данный метод и указанный тип файла


Fileinfo::addClass('className') - добавляет новый класс-обработчик
При добавлении класса-обработчика извлекается из него обрабатываемые mime-типы, если указаны, используемые методы и их алиасы
