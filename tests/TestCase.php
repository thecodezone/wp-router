<?php

namespace Tests;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase as TestBase;
abstract class TestCase extends TestBase {
	public function setUp(): void {
		global $__test_logged_in;

		$__test_logged_in = false;
	}

	protected function container(): Container {
		return new Container();
	}
}