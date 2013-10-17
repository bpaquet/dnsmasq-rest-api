<?php

require_once 'zones.php';

class WriteZoneTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    $this->path = exec("mktemp -d -t test.XXXXXXXXXXX");
    $this->zone = new Zone($this->path);
    file_put_contents($this->path."/zone1", "127.0.0.1 toto");
  }

  function tearDown() {
    system("rm -rf ".$this->path);
  }

  function testSetup() {
    $this->assertEquals(array("127.0.0.1" => array("toto")), $this->zone->get_zone("zone1"));
  }

  function testNewZone() {
    $this->assertEquals(true, $this->zone->add_record("zone2", "127.0.0.2", "tata"));
    $this->assertEquals(array("zone1", "zone2"), $this->zone->list_zones());
    $this->assertEquals(array("127.0.0.1" => array("toto")), $this->zone->get_zone("zone1"));
    $this->assertEquals(array("127.0.0.2" => array("tata")), $this->zone->get_zone("zone2"));
  }

  function testAddNewRecord() {
    $this->assertEquals(true, $this->zone->add_record("zone1", "127.0.0.2", "tata"));
    $this->assertEquals(array("zone1"), $this->zone->list_zones());
    $this->assertEquals(array("127.0.0.1" => array("toto"), "127.0.0.2" => array("tata")), $this->zone->get_zone("zone1"));
  }

  function testAddRecord() {
    $this->assertEquals(true, $this->zone->add_record("zone1", "127.0.0.1", "tata"));
    $this->assertEquals(array("zone1"), $this->zone->list_zones());
    $this->assertEquals(array("127.0.0.1" => array("toto", "tata")), $this->zone->get_zone("zone1"));
  }

  function testDeleteRecord() {
    $this->assertEquals(true, $this->zone->delete_record("zone1", "127.0.0.1"));
    $this->assertEquals(array("zone1"), $this->zone->list_zones());
    $this->assertEquals(array(), $this->zone->get_zone("zone1"));
  }

  function testDeleteRecordNotExists() {
    $this->assertEquals(true, $this->zone->delete_record("zone1", "127.0.0.2"));
    $this->assertEquals(array("zone1"), $this->zone->list_zones());
    $this->assertEquals(array("127.0.0.1" => array("toto")), $this->zone->get_zone("zone1"));
  }

  function testDeleteRecordWithOtherContent() {
    $this->assertEquals(true, $this->zone->add_record("zone1", "127.0.0.2", "tata"));
    $this->assertEquals(true, $this->zone->delete_record("zone1", "127.0.0.2"));
    $this->assertEquals(array("zone1"), $this->zone->list_zones());
    $this->assertEquals(array("127.0.0.1" => array("toto")), $this->zone->get_zone("zone1"));
  }

}