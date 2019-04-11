<?php

use Contracts\FileExplorer;

class BasicFileinfo extends FileExplorer
{
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
     * @return int|false
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
     * Возвращает размер файла.
     *
     * @return int|false
     */
    public function getSizeProperty()
    {
        if ($this->filename) {
            return filesize($this->filename);
        }

        return false;
    }
}
