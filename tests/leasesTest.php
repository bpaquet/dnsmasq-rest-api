<?php

require_once 'leases.php';

class LeasesTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    $this->leases = new LeasesReader("tests/data/leases");
    $this->l1 = array(
      "timestamp" => 1384349107,
      "mac" => "52:54:00:68:4d:74",
      "ip" => "10.1.126.4",
      "hostname" => "toto",
      "client_id" => "01:52:54:00:68:4d:74"
    );
    $this->l2 = array(
      "timestamp" => 1384349327,
      "mac" => "52:54:00:2a:36:c2",
      "ip" => "10.1.118.125",
      "hostname" => "*",
      "client_id" => "*"
    );
  }

  function testExists() {
    $this->assertEquals(true, $this->leases->exists());
  }

  function testNotExists() {
    $this->leases = new LeasesReader("tests/data/leases_not_exists");
    $this->assertEquals(false, $this->leases->exists());
  }

  function testReadAll() {
    $this->assertEquals(array($this->l1, $this->l2), $this->leases->read_all());
  }

  function testFindByIp() {
    $this->assertEquals(array($this->l1), $this->leases->find("ip", "10.1.126.4"));
  }

  function testFindByIpRegex() {
    $this->assertEquals(array($this->l2), $this->leases->find("ip", ".*.125$"));
  }

  function testFindByIpNotFound() {
    $this->assertEquals(array(), $this->leases->find("ip", ".*.42$"));
  }

  function testFindByIpAll() {
    $this->assertEquals(array($this->l1, $this->l2), $this->leases->find("ip", ".*"));
  }

  function testFindByMac() {
    $this->assertEquals(array($this->l2), $this->leases->find("mac", "2a:36:c2"));
  }

}