<?php

require_once 'zones.php';

class DeleteZoneTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    $this->path = exec("mktemp -d -t test.XXXXXXXXXXX");
    $this->zone = new Zones($this->path);
  }

  function tearDown() {
    system("rm -rf ".$this->path);
  }

  function testDelete() {
    $this->assertEquals(array(), $this->zone->list_zones());
    file_put_contents($this->path."/zone1", "127.0.0.1 toto");
    $this->assertEquals(array("zone1"), $this->zone->list_zones());
    $this->assertEquals(true, $this->zone->delete_zone("zone1"));
    $this->assertEquals(array(), $this->zone->list_zones());
  }

  function testDeleteNotExists() {
    $this->assertEquals(false, $this->zone->delete_zone("zone1"));
  }

}