<?php 

/*
Php class used for CSV treatment in ms_admininfo

- save tmp file
- open file
- close file
- get header
- get line
- delete tmp file
*/

class CSV {
    public $champs = array('TMP_DIR' => 'TMP_DIR', 'EXPORT_SEP' => 'EXPORT_SEP');

    function __construct() {
        $values = look_config_default_values($this->champs);
        $this->separator = $values['tvalue']['EXPORT_SEP'];
        $this->file_path = $values['tvalue']['TMP_DIR'];
    }

    function saveCSV($file, $newname) {
        // save uploaded csv to dl dir
        $info = pathinfo($file['name']);
        // $target will be defined later in interface 
        $target = $this->file_path."/tmp_dir/".$newname;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $this->file = $target; 
        } else {
            return false;
        }
    }

    function openCSV($filename) {
        return $this->handle = fopen($filename, 'r');
    }


    // get first line from file > header
    function readCSVHeader() {
        if (($line = fgets($this->handle)) != false) {
            // remove whitespaces at end and beginning
            $header = array_map('trim', explode($this->separator, $line));
            if (count($header) <= 1) {
                return false;
            } else {
                // remove single and double quotes from string
                foreach ($header as $key => $column) {
                    $columns[$key] = str_replace(array("'", '"'), "", $column);
                }
                $this->header = $columns;
                return $this->header;
            }
        }
    }

    function readCSVLine() {
        if (($line = fgets($this->handle)) != false) {
            $row = array_map('trim', explode($this->separator, $line));
            foreach ($row as $key => $value) {
                $value = trim($value);
                $row[$key] = str_replace(array("'", '"'), "", $value);

            }
            return $row;
        } else {
            return false;
        }
    }


    function deleteCSV($filename) {
        if (file_exists($filename)) {
            unlink($filename);
        }

    }
}

