<?php

namespace CupOfTea\SemVer\Contracts;

interface VersionWithMutablePrefix extends Version
{
    /**
     * Create a new Version instance from the given components.
     *
     * @param  int  $major
     * @param  int  $minor
     * @param  int  $patch
     * @param  string|null  $prerelease
     * @param  string|null  $build
     * @param  bool  $hasPrefix
     * @return static
     */
    public static function create(int $major = 0, int $minor = 0, int $patch = 0, ?string $prerelease = null, ?string $build = null, bool $hasPrefix = true): VersionWithMutablePrefix;

    /**
     * Enable the 'v' prefix.
     *
     * @return \CupOfTea\SemVer\Contracts\VersionWithMutablePrefix
     */
    public function withPrefix(): VersionWithMutablePrefix;

    /**
     * Disable the 'v' prefix.
     *
     * @return \CupOfTea\SemVer\Contracts\VersionWithMutablePrefix
     */
    public function withoutPrefix(): VersionWithMutablePrefix;

    /**
     * Enable or disable the 'v' prefix.
     *
     * @param  bool  $prefix
     * @return void
     */
    public function setPrefix(bool $prefix): void;
}