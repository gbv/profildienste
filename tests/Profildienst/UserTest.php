<?php

use Profildienst\User;

/**
 * Created by PhpStorm.
 * User: luca
 * Date: 17.05.16
 * Time: 13:47
 */

class UserTest extends PHPUnit_Framework_TestCase {

  public function testSingleton(){
    $this->assertInstanceOf(User::class, User::getInstance());
  }

  public function testNameNotInit(){
    $user = User::getInstance();
    $this->expectException(Exception::class);
    $user->getName();
  }

  public function testIDNotInit(){
    $user = User::getInstance();
    $this->expectException(Exception::class);
    $user->getID();
  }

  public function testDoubleInit(){
    $user = User::getInstance();
    $user->initialize('Prename Surname', '1234');
    $this->expectException(Exception::class);
    $user->initialize('Prename Surname', '1234');
  }

  public function testGetAndSet(){
    $user = User::getInstance();
    $this->assertEquals('Prename Surname', $user->getName());
    $this->assertEquals('1234', $user->getID());
  }


}