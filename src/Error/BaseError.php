<?php

namespace monsieurluge\Result\Error;

use monsieurluge\Result\Error\Error;

/**
 * A generic Error.
 * @immutable
 */
final class BaseError implements Error
{
    /** @var string **/
    private $code;
    /** @var string **/
    private $message;

    public function __construct(string $code, string $message)
    {
        $this->code    = $code;
        $this->message = $message;
    }

    /**
    * @inheritDoc
    */
    public function code(): string
    {
        return $this->code;
    }

     /**
     * @inheritDoc
     */
    public function message(): string
    {
        return $this->message;
    }
}
