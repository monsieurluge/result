<?php

namespace monsieurluge\Result\ErrorFactory;

use InvalidArgumentException;
use monsieurluge\Result\Error\Error;

final class FileErrorFactory implements ErrorFactory
{
    /** @var string */
    private $path;

    public function __construct(string $configFile)
    {
        $this->checkFile($configFile);

        $this->path = $configFile;
    }

    /**
     * @inheritDoc
     */
    public function create(string $name, array $replacements = []): Error
    {
        throw new \RuntimeException(sprintf('method %s::%s not implemented', __CLASS__, __FUNCTION__));
    }

    /**
     * Checks if the config file exist.
     *
     * @param string $path
     *
     * @throws InvalidArgumentException if the file doesn't exist
     */
    private function checkFile(string $path): void
    {
        if (false === file_exists($path)) {
            throw new InvalidArgumentException(sprintf(
                'the provided error\'s configuration file (%s) doesn\'t exist',
                $path
            ));
        }
    }
}
