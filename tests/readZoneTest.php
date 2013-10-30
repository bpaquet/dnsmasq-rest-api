<?php

require_once 'zones.php';

class ReadZoneTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    $this->zone = new Zones("tests/data/read");
  }

  function testListZoneWrongPath() {
    $z = new Zones("toto");
    $this->assertEquals(array(), $z->list_zones());
  }

  function testListZone() {
    $this->assertEquals(array("empty", "zone1", "zone2", "zone3"), $this->zone->list_zones());
  }

  function testGetWrongZone() {
    $this->assertEquals(array(), $this->zone->get_zone("toto"));
  }

  function testGetZone2() {
    $this->assertEquals(array("140.1.2.3" => array("abc")), $this->zone->get_zone("zone2"));
  }

  function testGetZone3() {
    $this->assertEquals(array("127.0.0.1" => array("toto1", "toto3"), "127.0.0.2" => array("toto2")), $this->zone->get_zone("zone3"));
  }

  function testGetZone1() {
    $result = array(
      "127.0.0.1" => array("toto"),
      "127.0.0.2" => array("tata"),
      "127.0.0.3" => array("titi"),
      "127.0.0.4" => array("a", "b", "c", "d"),
    );
    $this->assertEquals($result, $this->zone->get_zone("zone1"));
  }

  function testGetZoneEmpty() {
    $this->assertEquals(array(), $this->zone->get_zone("empty"));
  }

}