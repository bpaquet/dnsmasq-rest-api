<?php

function list_zones() {
  global $host_d_path;
  $zones = scandir($host_d_path);
  sort($zones);
  array_shift($zones);
  array_shift($zones);
  return $zones;
}

function get_zone($name) {
  global $host_d_path;
  $content = file_get_contents($host_d_path . "/" . $name);
  $content = split("\n", $content);
  $content = array_filter($content, function($x) {
    return $x != "";
  });
  $map = array();
  foreach($content as $x) {
    $s = split(" ", $x);
    $ip = array_shift($s);
    $map[$ip] = $s;
  }
  return $map;
}