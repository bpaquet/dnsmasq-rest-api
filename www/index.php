<?php

require './config.php';
require './zones.php';

$request = $_SERVER["REQUEST_URI"];

function send_json($o) {
  header('Content-type: application/json');
  echo json_encode($o);
}

function send_404($msg = "Not found !") {
  header("HTTP/1.0 404 Not Found");
  echo $msg;
}

function send_ok($res, $msg = "") {
  if ($res === false) {
    header("HTTP/1.0 500 Error");
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

if (preg_match("/zones$/", $request)) {
  $zone = new Zone($host_d_path);
  send_json($zone->list_zones());
}
else if (preg_match("/zones\/([^\/]*)$/", $request, &$matches)) {
  $z = $matches[1];
  $zone = new Zone($host_d_path);
  if (in_array($z, $zone->list_zones())) {
    send_json($zone->get_zone($z));
  }
  else {
    send_404("Zone not found " . $z);
  }
}
else if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)\/([^\/]*)$/", $request, &$matches) && $_SERVER["REQUEST_METHOD"] == "GET") {
  $zone = new Zone($host_d_path);
  send_ok($zone->add_record($matches[1], $matches[2], $matches[3]), "Record added");
}
else if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)$/", $request, &$matches) && $_SERVER["REQUEST_METHOD"] == "DELETE") {
  $zone = new Zone($host_d_path);
  send_ok($zone->delete_record($matches[1], $matches[2]), "Record deleted");
}
else {
  send_404();
}
