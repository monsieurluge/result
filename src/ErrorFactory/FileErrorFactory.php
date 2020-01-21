<?php

namespace monsieurluge\Result\ErrorFactory;

use monsieurluge\Result\Error\Error;

final class FileErrorFactory implements ErrorFactory
{
    /**
     * @inheritDoc
     */
    public function create(string $name, array $replacements = []): Error
    {
        throw new \RuntimeException(sprintf('method %s::%s not implemented', __CLASS__, __FUNCTION__));
    }
}
