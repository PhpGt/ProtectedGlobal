<?php
namespace Gt\ProtectedGlobal\Test;

use Gt\ProtectedGlobal\ProtectedGlobal;
use Gt\ProtectedGlobal\Protection;
use Gt\ProtectedGlobal\ProtectedGlobalException;
use PHPUnit\Framework\TestCase;

class ProtectionTest extends TestCase {
	public function testRemoveGlobals() {
		$testGlobals = [
			"_ENV" => [
				"somekey" => "somevalue",
			]
		];
		self::assertArrayHasKey("somekey", $testGlobals["_ENV"]);
		Protection::removeGlobals($testGlobals);
		self::assertArrayNotHasKey("somekey", $testGlobals["_ENV"]);
		self::assertNotNull($testGlobals);
	}

	public function testOverride() {
		$env = ["somekey" => "somevalue"];
		$server = [];
		$get = [];
		$post = [];
		$files = [];
		$cookie = [];
		$session = [];
		$testGlobals = [
			"_ENV" => $env,
		];
		self::assertEquals("somevalue", $testGlobals["_ENV"]["somekey"]);
		self::assertEquals("somevalue", $env["somekey"]);
		Protection::overrideInternals(
			$testGlobals,
			$env,
			$server,
			$get,
			$post,
			$files,
			$cookie,
			$session
		);

		self::assertInstanceOf(ProtectedGlobal::class, $env);
		self::assertEquals("somevalue", $env["somekey"]);
	}

	public function testWhitelist() {
		$env = ["somekey" => "somevalue", "anotherkey" => "anothervalue"];
		$server = [];
		$get = [];
		$post = [];
		$files = [];
		$cookie = [];
		$session = [];
		$testGlobals = [
			"_ENV" => $env,
		];
		Protection::removeGlobals($env, [
			"env" => "anotherkey",
		]);
		Protection::overrideInternals(
			$testGlobals,
			$env,
			$server,
			$get,
			$post,
			$files,
			$cookie,
			$session
		);

		self::assertEquals("anothervalue", $env["anotherkey"]);
		self::expectException(ProtectedGlobalException::class);
		$variable = $env["somevalue"];
	}

	public function testWhitelistMany() {
		$env = ["somekey" => "somevalue", "anotherkey" => "anothervalue"];
		$server = ["serverkey1" => "servervalue1"];
		$get = [];
		$post = ["postkey1" => "postvalue1", "postkey2" => "postvalue2"];
		$files = [];
		$cookie = [];
		$session = [];
		$testGlobals = [
			"_ENV" => $env,
		];
		Protection::removeGlobals($env);
		Protection::removeGlobals($post, [
			"env" => [],
			"post" => "postkey2",
		]);
		Protection::overrideInternals(
			$testGlobals,
			$env,
			$server,
			$get,
			$post,
			$files,
			$cookie,
			$session
		);

		self::assertEquals("postvalue2", $env["postkey1"]);
		self::expectException(ProtectedGlobalException::class);
		$variable = $post["postkey1"];
	}
}