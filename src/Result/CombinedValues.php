<?php

namespace monsieurluge\Result\Result;

interface CombinedValues
{

    /**
     * Returns the first result value.
     *
     * @return mixed
     */
    public function first();

    /**
    * Returns the second result value.
    *
    * @return mixed
    */
    public function second();

}
