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

  function send_json($o) {
    $this->output->setContentType('application/json');
    $this->output->write(json_encode($o) . "\n");
  }

  function send_ok($condition, $msg = "") {
    if ($condition === false) {
      $this->output->setReturnCode(500, "Error");
      $this->output->setContentType('text/plain');
      $res = "Error";
      $this->output->write();
      if ($msg_error != "") {
        $res .= " ".$msg_error;
      }
      $this->output->write($res."\n");
    }
    else {
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
    }
    else if (preg_match("/zones$/", $request)) {
      $this->send_json($this->zones->list_zones());
    }
    else if (preg_match("/zones\/([^\/]*)$/", $request, $matches)) {
      $z = $matches[1];
      if (in_array($z, $this->zones->list_zones())) {
        if ($method == "DELETE") {
          $this->send_ok($this->zones->delete_zone($z), "Zone deleted");
        }
        else {
          $this->send_json($this->zones->get_zone($z));
        }
      }
      else {
        $this->send_404("Zone not found " . $z);
      }
    }
    else if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)\/([^\/]*)$/", $request, $matches) && $method == "GET") {
      $this->send_ok($this->zones->add_record($matches[1], $matches[2], $matches[3]), "Record added");
    }
    else if (preg_match("/zones\/([^\/]*)\/records\/([^\/]*)$/", $request, $matches) && $method == "DELETE") {
      $this->send_ok($this->zones->delete_record($matches[1], $matches[2]), "Record deleted");
    }
    else {
      $this->send_404();
    }
  }

}