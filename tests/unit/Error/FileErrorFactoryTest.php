<?php

namespace tests\unit\Error;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use monsieurluge\Result\ErrorFactory\FileErrorFactory;

final class FileErrorFactoryTest extends TestCase
{
    /**
     * @covers monsieurluge\Result\ErrorFactory\FileErrorFactory::__construct
     */
    public function testExceptionIsThrownWhenConfigFileDoesntExist()
    {
        // GIVEN a wrong config file path
        $file = '/foo/bar/foo/bar';
        // AND the expected exception
        $this->expectException(InvalidArgumentException::class);

        // WHEN the error factory object is created
        new FileErrorFactory($file);

        // THEN the expected exception is thrown
    }
}
