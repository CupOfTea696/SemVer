<?php

namespace CupOfTea\SemVer\Contracts;

interface ConvertableVersion
{
    /**
     * Convert the Version instance to the given implementation.
     *
     * @param  string  $implementation
     * @return \CupOfTea\SemVer\Contracts\Version
     */
    public function convertTo(string $implementation): Version;
}