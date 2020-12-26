<?php

namespace CupOfTea\SemVer\Concerns;

use CupOfTea\SemVer\InvalidArgumentException;
use CupOfTea\SemVer\Contracts\VersionWithMutablePrefix;
use CupOfTea\SemVer\Contracts\Version as VersionContract;

trait ConvertsVersionImplementations
{
    /**
     * Convert the Version instance to the given implementation.
     *
     * @param  string  $implementation
     * @return \CupOfTea\SemVer\Contracts\Version
     *
     * @throws \CupOfTea\SemVer\InvalidArgumentException
     */
    public function convertTo(string $implementation): VersionContract
    {
        if (! class_exists($implementation)) {
            throw new InvalidArgumentException(sprintf('The class %s does not exist', $implementation));
        }

        $interfaces = class_implements($implementation);

        if (! $interfaces || ! in_array(VersionContract::class, $interfaces)) {
            throw new InvalidArgumentException(sprintf('The given implementation must implement %s', VersionContract::class));
        }

        if (in_array(VersionWithMutablePrefix::class, $interfaces)) {
            return $implementation::create(
                $this->major,
                $this->minor,
                $this->patch,
                $this->prerelease,
                $this->build,
                $this->hasPrefix()
            );
        }

        return $implementation::create($this->major, $this->minor, $this->patch, $this->prerelease, $this->build);
    }
}