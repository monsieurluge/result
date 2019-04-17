<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;

/**
 * A failed result holding an Error.
 * @immutable
 */
final class Failure implements Result
{

    /** @var Error **/
    private $error;

    /**
     * @codeCoverageIgnore
     *
     * @param Error $error
     */
    public function __construct(Error $error)
    {
        $this->error = $error;
    }

    /**
     * @inheritDoc
     */
    public function else(Closure $doSomethingWithError): Result
    {
        ($doSomethingWithError)($this->error);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValueOrExecOnFailure(Closure $expression)
    {
        return ($expression)($this->error);
    }

    /**
     * @inheritDoc
     * @return Result a failed Result
     */
    public function map(Closure $expression): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     * @return Result a failed Result
     */
    public function mapOnFailure(Closure $expression): Result
    {
        return new self(
            ($expression)($this->error)
        );
    }

    public function then(Closure $action): Result
    {
        return $this;
    }

}
