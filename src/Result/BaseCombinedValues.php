<?php

namespace monsieurluge\Result\Result;

/**
 * A simple implementation of the CombinedValues interface.
 */
final class BaseCombinedValues implements CombinedValues
{

    /** @var mixed **/
    private $first;
    /** @var mixed **/
    private $second;

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $first
     * @param mixed $second
     */
    public function __construct($first, $second)
    {
        $this->first  = $first;
        $this->second = $second;
    }

    /**
    * @inheritDoc
    * @codeCoverageIgnore
    */
    public function first()
    {
        return $this->first;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function second()
    {
        return $this->second;
    }

}
