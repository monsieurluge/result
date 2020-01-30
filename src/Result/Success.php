<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Result\Result;

/**
 * A successful result holding a value.
 * @immutable
 */
final class Success implements Result
{

    /** @var mixed **/
    private $value;

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function else(Closure $doSomethingWithError): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flatMap(Closure $doSomething): Result
    {
        return ($doSomething)($this->value);
    }

    /**
     * @inheritDoc
     */
    public function getValueOrExecOnFailure(Closure $expression)
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function map(Closure $mutate): Result
    {
        return new self(
            ($mutate)($this->value)
        );
    }

    /**
     * @inheritDoc
     */
    public function mapOnFailure(Closure $mutateError): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function then(Closure $doSomething): Result
    {
        return ($doSomething)($this->value);
    }

}
