<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Result;

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
    public function flatMap(Closure $doSomething): Result
    {
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
     */
    public function map(Closure $mutate): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mapOnFailure(Closure $expression): Result
    {
        return new self(
            ($expression)($this->error)
        );
    }

    /**
     * @inheritDoc
     */
    public function then(Closure $doSomething): Result
    {
        return $this;
    }

}
