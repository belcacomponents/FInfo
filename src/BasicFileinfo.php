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
        if ($this->file) {
            return filectime($this->file) > filemtime($this->file)
                    ? filemtime($this->file)
                    : filectime($this->file);
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
        if ($this->file) {
            return filemtime($this->file);
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
        if ($this->file) {
            return filesize($this->file);
        }

        return false;
    }
}
