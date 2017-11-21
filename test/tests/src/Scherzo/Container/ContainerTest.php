<?php

use PHPUnit\Framework\TestCase;
use Scherzo\Container\Container;

class ScherzoContainerTest extends TestCase {
    public function testHas() {
        $c = new Container;
        $c->test = true;
        $this->assertTrue($c->has('test'));
        $this->assertFalse($c->has('differentTest'));
    }
}
