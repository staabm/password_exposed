<?php

namespace DivineOmega\PasswordExposed\Tests;

use DivineOmega\DOFileCachePSR6\CacheItemPool;
use DivineOmega\PasswordExposed\Enums\PasswordStatus;
use DivineOmega\PasswordExposed\PasswordExposedChecker;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PasswordExposedByHashTest extends TestCase
{
    /** @var PasswordExposedChecker */
    private $checker;

    protected function setUp(): void
    {
        $cache = new FilesystemAdapter(__FILE__);
        $this->checker = new PasswordExposedChecker(null, $cache);
    }

    public function testFunctionExists()
    {
        $this->assertTrue(function_exists('password_exposed_by_hash'));
    }

    /**
     * @return array
     */
    public function exposedPasswordHashProvider()
    {
        return [
            [sha1('test')],
            [sha1('password')],
            [sha1('hunter2')],
        ];
    }

    /**
     * @dataProvider exposedPasswordHashProvider
     *
     * @param string $hash
     */
    public function testExposedPasswords($hash)
    {
        $this->assertEquals($this->checker->passwordExposedByHash($hash), PasswordStatus::EXPOSED);
        $this->assertEquals(password_exposed_by_hash($hash), PasswordStatus::EXPOSED);
        $this->assertTrue($this->checker->isExposedByHash($hash));
        $this->assertTrue(password_is_exposed_by_hash($hash));
    }

    public function testNotExposedPasswords()
    {
        $this->assertEquals(
            $this->checker->passwordExposedByHash($this->getPasswordHashUnlikelyToBeExposed()),
            PasswordStatus::NOT_EXPOSED
        );
        $this->assertEquals(
            password_exposed_by_hash($this->getPasswordHashUnlikelyToBeExposed()),
            PasswordStatus::NOT_EXPOSED
        );
        $this->assertEquals(
            $this->checker->isExposedByHash($this->getPasswordHashUnlikelyToBeExposed()),
            false
        );
        $this->assertEquals(
            password_is_exposed_by_hash($this->getPasswordHashUnlikelyToBeExposed()),
            false
        );
    }

    /**
     * @return string
     */
    private function getPasswordHashUnlikelyToBeExposed()
    {
        $faker = Factory::create();

        return sha1($faker->words(6, true));
    }
}
