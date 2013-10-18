<?php

require './config.php';
require './zones.php';

$request = $_SERVER["REQUEST_URI"];

function send_json($o) {
  header('Content-type: application/json');
  echo json_encode($o) . "\n";
}

function send_404($msg = "Not found !") {
  header("HTTP/1.0 404 Not Found");
  echo $msg;
}

function send_ok($res, $msg = "", $msg_error = "") {
  if ($res === false) {
    header("HTTP/1.0 500 Error");
    header('Content-type: text/plain');
    echo "Error";
    if ($msg_error != "") {
      echo " ".$msg_error;
    }
    echo "\n";
  }
  else {
    header("HTTP/1.0 200 OK");
    header('Content-type: text/plain');
    echo "OK";
    if ($msg != "") {
      echo " ".$msg;
    }
    echo "\n";
  }
}

if (preg_match("/reload$/", $request)) {
  exec($reload_command, $out, $return);
  send_ok($return == 0, "Dnmasq config reloaded", "Unable to reload dnmasq config ".implode("\n", $out));
}
else if (preg_match("/zones$/", $request)) {
  $zones = new Zones($host_d_path);
  send_json($zones->list_zones());
}
else if (preg_match("/zones\/([^\/]*)$/", $request, $matches)) {
  $z = $matches[1];
  $zones = new Zones($host_d_path);
  if (in_array($z, $zones->list_zones())) {
    if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
      send_ok($zones->delete_zone($z), "Zone deleted");
    }
    else {
      send_json($zones->get_zone($z));
    }
  }
  else {
    send_404("Zone not found " . $z);
  }
}
else if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)\/([^\/]*)$/", $request, $matches) && $_SERVER["REQUEST_METHOD"] == "GET") {
  $zones = new Zones($host_d_path);
  send_ok($zones->add_record($matches[1], $matches[2], $matches[3]), "Record added");
}
else if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)$/", $request, $matches) && $_SERVER["REQUEST_METHOD"] == "DELETE") {
  $zones = new Zones($host_d_path);
  send_ok($zones->delete_record($matches[1], $matches[2]), "Record deleted");
}
else {
  send_404();
}
