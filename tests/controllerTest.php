<?php

require_once 'controller.php';

class ControllerTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    $this->path = exec("mktemp -d -t test.XXXXXXXXXXX");
    $this->controller = new Controller($this->path, "echo toto > output", "tests/data/leases");
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

  function testWrongConfig() {
    $this->controller = new Controller("/not_exist", "echo toto > /tmp/toto", "");
    $this->stubOutput();

    $this->expectSetReturnCode(500, "Error");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("Error\n");
    $this->controller->dispatch("POST", "/zones/toto/127.0.0.1/localhost.test");
  }

  function test404() {
    $this->expectSetReturnCode(404, "Not found");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("Not found !\n");
    $this->controller->dispatch("GET", "/toto");
  }

  function testReload() {
    if (file_exists("output")) {
      unlink("output");
    }

    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Dnmasq config reloaded\n");
    $this->controller->dispatch("POST", "/reload");
    $this->assertEquals("toto\n", file_get_contents("output"));
    unlink("output");
  }

  function testListZones() {
    $this->expectSetReturnCode(200, "OK");
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

  function testJsonError() {
    $this->expectSetReturnCode(500, "Error");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("Error Bad json data\n");
    $this->controller->dispatch("POST", "/zones/toto", '{"127.0.0.1":"localhost.test","localhost.test2"]}');
  }

  function testManipulateRecords() {
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite("[]\n");
    $this->controller->dispatch("GET", "/zones");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record added\n");
    $this->controller->dispatch("POST", "/zones/toto/127.0.0.1/localhost.test");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('["toto"]'."\n");
    $this->controller->dispatch("GET", "/zones");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.1":["localhost.test"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record added\n");
    $this->controller->dispatch("POST", "/zones/toto", '{"127.0.0.1":["localhost.test2"]}');

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record added\n");
    $this->controller->dispatch("POST", "/zones/toto/127.0.0.2/localhost.toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.1":["localhost.test","localhost.test2"],"127.0.0.2":["localhost.toto"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record deleted\n");
    $this->controller->dispatch("DELETE", "/zones/toto/127.0.0.1/localhost.test");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.1":["localhost.test2"],"127.0.0.2":["localhost.toto"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record deleted\n");
    $this->controller->dispatch("DELETE", "/zones/toto/127.0.0.1/localhost.test2");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.2":["localhost.toto"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record deleted\n");
    $this->controller->dispatch("DELETE", "/zones/toto/127.0.0.2");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Zone deleted\n");
    $this->controller->dispatch("DELETE", "/zones/toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite("[]\n");
    $this->controller->dispatch("GET", "/zones");
  }

  function testBackupAndMutlipleSet() {
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK Record added\n");
    $this->controller->dispatch("POST", "/zones/toto", '{"127.0.0.1":["localhost.test","localhost.test2"],"127.0.0.2":["localhost.0"]}');

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"toto":{"127.0.0.1":["localhost.test","localhost.test2"],"127.0.0.2":["localhost.0"]}}'."\n");
    $this->controller->dispatch("GET", "/backup");
  }

  function testEmptyBackup() {
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{}'."\n");
    $this->controller->dispatch("GET", "/backup");
  }

  function testRestore() {
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("text/plain");
    $this->expectWrite("OK All zones restored : titi toto\n");
    $this->controller->dispatch("POST", "/restore", '{"titi":{},"toto":{"127.0.0.1":["localhost.test","localhost.test2"],"127.0.0.2":["localhost.0"]}}');

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('["titi","toto"]'."\n");
    $this->controller->dispatch("GET", "/zones");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{"127.0.0.1":["localhost.test","localhost.test2"],"127.0.0.2":["localhost.0"]}'."\n");
    $this->controller->dispatch("GET", "/zones/toto");

    $this->stubOutput();
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('{}'."\n");
    $this->controller->dispatch("GET", "/zones/titi");

  }

  function testReadLeases() {
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('[{"timestamp":"1384349107","mac":"52:54:00:68:4d:74","ip":"10.1.126.4","hostname":"toto","client_id":"01:52:54:00:68:4d:74"},{"timestamp":"1384349327","mac":"52:54:00:2a:36:c2","ip":"10.1.118.125","hostname":"*","client_id":"*"}]'."\n");
    $this->controller->dispatch("GET", "/leases");
  }

  function testReadLeasesFilter() {
    $this->expectSetReturnCode(200, "OK");
    $this->expectSetContentType("application/json");
    $this->expectWrite('[{"timestamp":"1384349327","mac":"52:54:00:2a:36:c2","ip":"10.1.118.125","hostname":"*","client_id":"*"}]'."\n");
    $this->controller->dispatch("GET", "/leases", null, array("ip" => "118.125"));
  }

}