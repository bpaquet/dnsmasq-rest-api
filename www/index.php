<?php

require './config.php';
require 'controller.php';

class Output {

  function setContentType($type) {
    header('Content-type: '.$type);
  }

  function write($s) {
    echo $s;
  }

  function setReturnCode($code, $text) {
    header("HTTP/1.0 ".$code." ".$text);
  }

}

$controller = new Controller($host_d_path, $reload_command, $lease_file);
$controller->setOutput(new Output());

$request = $_SERVER["REQUEST_URI"];
if (strlen($_SERVER["QUERY_STRING"]) > 0) {
  $request = substr($request, 0, strlen($_SERVER["REQUEST_URI"]) - strlen($_SERVER["QUERY_STRING"]) - 1);
}
$method = $_SERVER["REQUEST_METHOD"];

$controller->dispatch($method, $request, file_get_contents('php://input'), $_GET);