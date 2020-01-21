<?php

namespace tests\unit\ErrorFactory;

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

    /**
     * @covers monsieurluge\Result\ErrorFactory\FileErrorFactory::create
     */
    public function testExceptionIsThrownWhenConfigFileIsNotReadable()
    {
        // GIVEN a config file in which the JSON is incorrect
        $file = sprintf('%s/incorrectJsonFile.json', __DIR__);
        // AND the factory
        $factory = new FileErrorFactory($file);
        // AND the expected exception
        $this->expectException(InvalidArgumentException::class);

        // WHEN the error object is requested
        $factory->create('---');

        // THEN the expected exception is thrown
    }

    /**
     * @covers monsieurluge\Result\ErrorFactory\FileErrorFactory::create
     */
    public function testExceptionIsThrownWhenNoDefaultErrorIsProvided()
    {
        // GIVEN a config file in which no default error is provided
        $file = sprintf('%s/errorsWithoutDefault.json', __DIR__);
        // AND the error factory
        $factory = new FileErrorFactory($file);
        // AND the expected exception
        $this->expectException(InvalidArgumentException::class);

        // WHEN an unknown error is requested
        $factory->create('unknown');

        // THEN the expected exception is thrown
    }
}
