<?php

namespace monsieurluge\Result\Error;

interface Error
{
    /**
     * Returns the code.
     *
     * @return string
     */
    public function code(): string;

    /**
     * Returns the message.
     *
     * @return string
     */
    public function message(): string;
}
