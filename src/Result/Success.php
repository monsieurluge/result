<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Action\Action;
use monsieurluge\Result\Result\Result;

/**
 * A successful result holding a content.
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
    public function getValueOrExecOnFailure(Closure $expression)
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function map(Closure $expression): Result
    {
        return new self(
            ($expression)($this->value)
        );
    }

    /**
     * @inheritDoc
     */
    public function mapOnFailure(Closure $expression): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function then(Action $action): Result
    {
        return $action->process($this->value);
    }

    public function thenTemp(Closure $action): Result
    {
        return ($action)($this->value);
    }

}
