<?php

namespace monsieurluge\Result\Action;

use Closure;
use monsieurluge\Result\Action\Action;
use monsieurluge\Result\Result\Result;

/**
 * A generic action for which the behavior is determined at its construction.
 * @immutable
 */
final class CustomAction implements Action
{

    /** @var Closure **/
    private $expression;

    /**
     * @codeCoverageIgnore
     *
     * @param Closure $expression the expression to apply to the target which returns a Result.
     */
    public function __construct(Closure $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @inheritDoc
     */
    public function process($target): Result
    {
        return ($this->expression)($target);
    }

}
