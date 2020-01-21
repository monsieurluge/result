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

    /**
     * @covers monsieurluge\Result\ErrorFactory\FileErrorFactory::create
     */
    public function testDefaultErrorIsReturnedWhenErrorNameIsUnknown()
    {
        // GIVEN a config file in which a default error is provided (code: "foo-1", message: "bar")
        $file = sprintf('%s/errors.json', __DIR__);
        // AND the error factory
        $factory = new FileErrorFactory($file);

        // WHEN an unknown error is requested
        $error = $factory->create('unknown');

        // THEN the error's code is "foo"
        $this->assertSame('foo', $error->code());
        // AND the error's message is "bar"
        $this->assertSame('bar', $error->message());
    }

    /**
     * @covers monsieurluge\Result\ErrorFactory\FileErrorFactory::create
     */
    public function testExpectedErrorIsReturned()
    {
        // GIVEN a config file in which the following error is defined: name="test #1", code="test", message="test OK"
        $file = sprintf('%s/errorsWithoutDefault.json', __DIR__);
        // AND the error factory
        $factory = new FileErrorFactory($file);

        // WHEN a configured error is requested
        $error = $factory->create('test');

        // THEN the error's code is "test-001"
        $this->assertSame('test-001', $error->code());
        // THEN the error's message is "test OK"
        $this->assertSame('test OK', $error->message());
    }

    /**
     * @covers monsieurluge\Result\ErrorFactory\FileErrorFactory::create
     */
    public function testErrorMessageContainsRawTextWhenNoReplacementStringIsProvided()
    {
        // GIVEN a config file in which the following error is defined: name="test #1", code="test", message="test {{toReplace}} OK"
        $file = sprintf('%s/errors.json', __DIR__);
        // AND the error factory
        $factory = new FileErrorFactory($file);

        // WHEN a configured error is requested and no string replacement is provided
        $error = $factory->create('test');

        // THEN the error's code is "test-002"
        $this->assertSame('test-002', $error->code());
        // THEN the error's message is "test {{toReplace}} OK"
        $this->assertSame('test {{toReplace}} OK', $error->message());
    }

    /**
     * @covers monsieurluge\Result\ErrorFactory\FileErrorFactory::create
     */
    public function testErrorMessageHasBeenUpdated()
    {
        // GIVEN a config file in which the following error is defined: name="test #1", code="test", message="test {{toReplace}} OK"
        $file = sprintf('%s/errors.json', __DIR__);
        // AND the error factory
        $factory = new FileErrorFactory($file);
        // AND a replacement string
        $replaceBy = [ 'toReplace' => 'replaced' ];

        // WHEN a configured error is requested and no string replacement is provided
        $error = $factory->create('test', $replaceBy);

        // THEN the error's code is "test-002"
        $this->assertSame('test-002', $error->code());
        // THEN the error's message is "test replaced OK"
        $this->assertSame('test replaced OK', $error->message());
    }
}
