<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Home Controller Test
 */
class HomeControllerTest extends TestCase
{
    public function testHomeControllerExists(): void
    {
        $this->assertTrue(class_exists(\Gainz\Controllers\HomeController::class));
    }

    public function testHomeControllerHasIndexMethod(): void
    {
        $controller = new \Gainz\Controllers\HomeController();
        $this->assertTrue(method_exists($controller, 'index'));
    }
}
