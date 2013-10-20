<?php

require_once 'zones.php';

class Controller {

  function __construct($host_d_path, $reload_command, $lease_file) {
    $this->zones = new Zones($host_d_path);
    $this->reload_command = $reload_command;
  }

  function setOutput($output) {
    $this->output = $output;
  }

  function send_404($msg = "Not found !") {
    $this->output->setReturnCode(404, "Not found");
    $this->output->setContentType('text/plain');
    $this->output->write($msg."\n");
  }

  function send_json($o, $is_hash = false) {
    $this->output->setReturnCode(200, "OK");
    $this->output->setContentType('application/json');
    if ($is_hash && count($o) == 0) {
      $this->output->write("{}" . "\n");
    }
    else {
      $this->output->write(json_encode($o) . "\n");
    }
  }

  function send_ok($condition, $msg, $msg_error) {
    if ($condition === false) {
      $this->output->setReturnCode(500, "Error");
      $this->output->setContentType('text/plain');
      $res = "Error";
      if ($msg_error != "") {
        $res .= " ".$msg_error;
      }
      $this->output->write($res."\n");
    }
    else {
      $this->output->setReturnCode(200, "OK");
      $this->output->setContentType('text/plain');
      $res = "OK";
      if ($msg != "") {
        $res .= " ".$msg;
      }
      $this->output->write($res."\n");
    }
  }

  function dispatch($method, $request, $body = null) {
    if (preg_match("/reload$/", $request)) {
      exec($reload_command, $out, $return);
      $this->send_ok($return == 0, "Dnmasq config reloaded", "Unable to reload dnmasq config ".implode("\n", $out));
      return;
    }
    if (preg_match("/backup$/", $request)) {
      $zz = $this->zones->list_zones();
      $result = array();
      foreach($zz as $z) {
        $result[$z] = $this->zones->get_zone($z);
      }
      $this->send_json($result, true);
      return;
    }
    if (preg_match("/restore$/", $request) && $method == "POST") {
      $content = json_decode($body, true);
      if ($content === null) {
        $this->send_ok(false, "", "Bad json data");
        return;
      }
      $zz = $this->zones->list_zones();
      foreach($zz as $z) {
        if (!$this->zones->delete_zone($z)) {
          send_ok(false, "", "Unable to delete zone ".$z);
          return;
        };
      }
      foreach(array_keys($content) as $z) {
        if (!$this->zones->set_zone($z, $content[$z])) {
          send_ok(false, "", "Unable to import zone ".$z);
          return;
        }
      }
      $this->send_ok(true, "All zones restored : ".implode($this->zones->list_zones(), " "), "");
      return;
    }
    if (preg_match("/zones$/", $request)) {
      $this->send_json($this->zones->list_zones());
      return;
    }
    if (preg_match("/zones\/([^\/]*)$/", $request, $matches)) {
      $z = $matches[1];
      if (in_array($z, $this->zones->list_zones())) {
        if ($method == "DELETE") {
          $this->send_ok($this->zones->delete_zone($z), "Zone deleted", "");
        }
        else {
          $this->send_json($this->zones->get_zone($z), true);
        }
      }
      else {
        $this->send_404("Zone not found " . $z);
      }
      return;
    }
    if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)\/([^\/]*)$/", $request, $matches) && $method == "GET") {
      $this->send_ok($this->zones->add_record($matches[1], $matches[2], $matches[3]), "Record added", "");
      return;
    }
    if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)$/", $request, $matches) && $method == "DELETE") {
      $this->send_ok($this->zones->delete_record($matches[1], $matches[2]), "Record deleted", "");
      return;
    }
    $this->send_404();
  }

}