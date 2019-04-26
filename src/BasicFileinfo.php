<?php

namespace Belca\FInfo;

use Belca\FInfo\FileExplorer;

class BasicFileinfo extends FileExplorer
{
    /**
     * Список алиасов (синонимов) виртуальных свойств (вызываемых методов).
     *
     * @var array
     */
    protected static $aliases = [
        'filesize' => 'size',
    ];

    /**
     * Работает со всеми типами файла.
     *
     * @return boolean
     */
    public function checkCompatibility()
    {
        return true;
    }

    /**
     * Возвращает время изменения индексного дескриптора файла
     * или время последнего изменения файла (наименьшее значение) с целью
     * получения даты создания файла.
     *
     * @return int
     */
    public function getCreatedProperty()
    {
        if ($this->filename) {
            return filectime($this->filename) > filemtime($this->filename)
                    ? filemtime($this->filename)
                    : filectime($this->filename);
        }

        return false;
    }

    /**
     * Возвращает время последнего изменения файла.
     *
     * @return int
     */
    public function getEditedProperty()
    {
        if ($this->filename) {
            return filemtime($this->filename);
        }

        return false;
    }

    /**
     * Возвращает размер файла.
     *
     * @return int
     */
    public function getSizeProperty()
    {
        if ($this->filename) {
            return filesize($this->filename);
        }

        return false;
    }
}
