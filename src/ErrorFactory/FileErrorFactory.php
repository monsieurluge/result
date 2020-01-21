<?php

namespace monsieurluge\Result\ErrorFactory;

use InvalidArgumentException;
use monsieurluge\Result\Error\BaseError;
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
        $configuration = json_decode(strval(file_get_contents($this->path)), true);

        if (is_null($configuration)) {
            throw new InvalidArgumentException(sprintf(
                'cannot read the error\'s configuration file "%s", the json may be incorrect',
                $this->path
            ));
        }

        return isset($configuration[$name])
            ? $this->createError($configuration[$name], $replacements)
            : $this->createDefaultError($configuration);
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
                'the provided error\'s configuration file "%s" doesn\'t exist',
                $path
            ));
        }
    }

    /**
     * Creates the default configured Error.
     *
     * @param array<string,array<string,string>> $configuration
     *
     * @return Error
     * @throws InvalidArgumentException
     */
    private function createDefaultError(array $configuration): Error
    {
        if (false === isset($configuration['default'])) {
            throw new InvalidArgumentException(sprintf(
                'cannot create an Error: there is no default configuration in the file "%s"',
                $this->path
            ));
        }

        return new BaseError(
            $configuration['default']['code'],
            $configuration['default']['message']
        );
    }

    /**
     * Creates the named Error.
     *
     * @param array<string,string> $raw          the raw errors dictionary
     * @param array<string,string> $replacements a string replacement list as follows: [ 'name'=>'replacement', 'name 2'=>'replacement 2'... ]
     *
     * @return Error
     */
    private function createError(array $raw, array $replacements): Error
    {
        $message = $raw['message'];

        foreach ($replacements as $name => $replacement) {
            $message = str_replace(sprintf('{{%s}}', $name), $replacement, $message);
        }

        return new BaseError(
            $raw['code'],
            $message
        );
    }
}
