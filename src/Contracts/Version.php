<?php

namespace CupOfTea\SemVer\Contracts;

use Stringable;
use JsonSerializable;

interface Version extends JsonSerializable, Stringable
{
    /**
     * Create a new Version instance.
     *
     * @param  string  $version
     * @return void
     */
    public function __construct(string $version = 'v0.1.0');

    /**
     * Create a new Version instance from the given components.
     *
     * @param  int  $major
     * @param  int  $minor
     * @param  int  $patch
     * @param  string|null  $prerelease
     * @param  string|null  $build
     * @return static
     */
    public static function create(int $major = 0, int $minor = 0, int $patch = 0, ?string $prerelease = null, ?string $build = null): Version;

    /**
     * Checks if the Version has a 'v' prefix.
     *
     * @return bool
     */
    public function hasPrefix(): bool;

    /**
     * Get the major version component.
     *
     * @return int
     */
    public function getMajor(): int;

    /**
     * Set the major version component.
     *
     * @param  int  $major
     * @return void
     */
    public function setMajor(int $major): void;

    /**
     * Get the minor version component.
     *
     * @return int
     */
    public function getMinor(): int;

    /**
     * Set the minor version component.
     *
     * @param  int  $minor
     * @return void
     */
    public function setMinor(int $minor): void;

    /**
     * Get the patch version component.
     *
     * @return int
     */
    public function getPatch(): int;

    /**
     * Set the patch version component.
     *
     * @param  int  $patch
     * @return void
     */
    public function setPatch(int $patch): void;

    /**
     * Check if this Version is a pre-release.
     *
     * @return bool
     */
    public function isPrerelease(): bool;

    /**
     * Get the pre-release version component.
     *
     * @return string|null
     */
    public function getPrerelease(): ?string;

    /**
     * Set the pre-release version component.
     *
     * @param  string|null  $prerelease
     * @return void
     */
    public function setPrerelease(?string $prerelease): void;

    /**
     * Unset the pre-release version component.
     */
    public function unsetPrerelease(): void;

    /**
     * Check if the Version has build metadata.
     *
     * @return bool
     */
    public function hasBuild(): bool;

    /**
     * Get the build metadata.
     *
     * @return string|null
     */
    public function getBuild(): ?string;

    /**
     * Set the build metadata.
     *
     * @param  string|null  $build
     * @return void
     */
    public function setBuild(?string $build): void;

    /**
     * Unset the build metadata.
     *
     * @return void
     */
    public function unsetBuild(): void;

    /**
     * Bump the patch major component.
     *
     * @param  int  $by
     * @return $this
     */
    public function bumpMajor(int $by = 1): Version;

    /**
     * Bump the minor version component.
     *
     * @param  int  $by
     * @return $this
     */
    public function bumpMinor(int $by = 1): Version;

    /**
     * Bump the patch version component.
     *
     * @param  int  $by
     * @return $this
     */
    public function bumpPatch(int $by = 1): Version;

    /**
     * Clear the pre-release and build meta.
     *
     * @return $this
     */
    public function release(): Version;

    /**
     * Get the version string.
     *
     * @return string
     */
    public function getVersion(): string;
}