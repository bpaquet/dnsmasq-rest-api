<?php

class LeasesReader {

  function __construct($lease_file) {
    $this->file = $lease_file;
  }

  function exists() {
    return file_exists($this->file);
  }

  function read_all() {
    $result = array();
    $content = file_get_contents($this->file);
    foreach(explode("\n", $content) as $line) {
      if (trim($line) != "") {
        $ll = explode(" ", $line);
        array_push($result, array(
          'timestamp' => $ll[0],
          'mac' => $ll[1],
          'ip' => $ll[2],
          'hostname' => $ll[3],
          'client_id' => $ll[4],
        ));
      }
    }
    return $result;
  }

  function find($field, $value) {
    $result = array();
    foreach($this->read_all() as $r) {
      if (preg_match("/".$value."/", $r[$field])) {
        array_push($result, $r);
      }
    }
    return $result;
  }
}

