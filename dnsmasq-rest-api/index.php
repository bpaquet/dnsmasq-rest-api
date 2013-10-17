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

if (preg_match("/zones$/", $request)) {
  send_json(list_zones());
}
else if (preg_match("/zones\/(.*$)/", $request, &$matches)) {
  $z = $matches[1];
  if (array_search($z, list_zones()) !== false) {
    send_json(get_zone($z));
  }
  else {
    send_404("Zone not found " . $z);
  }
}
else {
  send_404();
}
