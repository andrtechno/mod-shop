<?php
namespace panix\mod\shop\components\forsage;


class ForsageProductImage extends CUploadedFile {

    private $_name;
    private $_tempName;
    private $_type;
    private $_size;
    private $_error;

    public function __construct($name, $tempName, $type, $size, $error) {
        $this->_name = $name;
        $this->_tempName = $tempName;
        $this->_type = $type;
        $this->_size = $size;
        $this->_error = $error;
        parent::__construct($name, $tempName, $type, $size, $error);
    }

    /**
     * @static
     * @param $fullPath
     * @return bool|CsvImage
     */
    public static function create($fullPath) {
        if (!file_exists($fullPath))
            return false;
        $name = explode(DIRECTORY_SEPARATOR, $fullPath);
        return new ForsageProductImage(end($name), $fullPath, CFileHelper::getMimeType($fullPath), filesize($fullPath), false);
    }

    /**
     * @param string $file
     * @param boolean $deleteTempFile
     * @return bool
     */
    public function saveAs($file, $deleteTempFile = true) {
        return copy($this->_tempName, $file);
    }

}