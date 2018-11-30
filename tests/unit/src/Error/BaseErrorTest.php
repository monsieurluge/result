<?php

namespace tests\unit\Error;

use monsieurluge\Result\Error\BaseError;
use PHPUnit\Framework\TestCase;

final class BaseErrorTest extends TestCase
{

    /**
     * @covers monsieurluge\Result\Error\BaseError::code
     */
    public function testGetCode()
    {
        // GIVEN
        $testSubject = new BaseError('err-1234', 'toto');

        // WHEN
        $code = $testSubject->code();

        // THEN
        $this->assertSame('err-1234', $code);
    }

    /**
     * @covers monsieurluge\Result\Error\BaseError::message
     */
    public function testGetMessage()
    {
        // GIVEN
        $testSubject = new BaseError('err-1234', 'toto');

        // WHEN
        $message = $testSubject->message();

        // THEN
        $this->assertSame('toto', $message);
    }

}
