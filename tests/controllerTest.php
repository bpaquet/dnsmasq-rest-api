<?php

require_once 'controller.php';

class ControllerTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    $this->path = exec("mktemp -d -t test.XXXXXXXXXXX");
    $this->controller = new Controller($this->path, "echo toto > /tmp/toto", "");
    $this->stubOutput();
  }

  function stubOutput() {
    $this->output = $this->getMock("Output", array("setReturnCode", "setContentType", "write"));
    $this->controller->setOutput($this->output);
  }

  function tearDown() {
    system("rm -rf ".$this->path);
  }

  function expectSetReturnCode($code, $text) {
    $this->output
      ->expects($this->once())
      ->method("setReturnCode")
      ->with($this->equalTo($code), $this->equalTo($text));
  }

  function expectSetContentType($content_type) {
    $this->output
      ->expects($this->once())
      ->method("setContentType")
      ->with($this->equalTo($content_type));
  }

  function expectWrite($s) {
    $this->output
      ->expects($this->once())
      ->method("write")
      ->with($this->equalTo($s));
  }

  function test404() {
    $this->expectSetReturnCode(404, "Not found");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("Not found !\n");
    $this->controller->dispatch("GET", "/toto");
  }

  function testListZones() {
    $this->expectSetContentType("application/json");
    $this->expectWrite("[]\n");
    $this->controller->dispatch("GET", "/zones");
  }

  function testZoneNotExistent() {
    $this->expectSetReturnCode(404, "Not found");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("Zone not found toto\n");
    $this->controller->dispatch("GET", "/zones/toto");
  }

  function testManipulateRecords() {
    $this->expectSetContentType("application/json");
    $this->expectWrite("[]\n");
    $this->controller->dispatch("GET", "/zones");

    $this->stubOutput();
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record added\n");
    $this->controller->dispatch("GET", "/zones/toto/records/127.0.0.1/localhost.test");

    $this->stubOutput();
    $this->expectSetContentType("application/json");
    $this->expectWrite('["toto"]'."\n");
    $this->controller->dispatch("GET", "/zones");

    $this->stubOutput();
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.1":["localhost.test"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record added\n");
    $this->controller->dispatch("GET", "/zones/toto/records/127.0.0.1/localhost.test2");

    $this->stubOutput();
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record added\n");
    $this->controller->dispatch("GET", "/zones/toto/records/127.0.0.2/localhost.toto");

    $this->stubOutput();
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.1":["localhost.test","localhost.test2"],"127.0.0.2":["localhost.toto"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record deleted\n");
    $this->controller->dispatch("DELETE", "/zones/toto/records/127.0.0.1");

    $this->stubOutput();
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.2":["localhost.toto"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Zone deleted\n");
    $this->controller->dispatch("DELETE", "/zones/toto");

    $this->stubOutput();
    $this->expectSetContentType("application/json");
    $this->expectWrite("[]\n");
    $this->controller->dispatch("GET", "/zones");
}

}