<?php

namespace CupOfTea\SemVer;

use CupOfTea\Package\Package;
use CupOfTea\SemVer\Contracts\ConvertableVersion;
use CupOfTea\Package\Contracts\Package as PackageContract;
use CupOfTea\SemVer\Concerns\ConvertsVersionImplementations;
use CupOfTea\SemVer\Contracts\VersionWithMutablePrefix as VersionContract;

class Version extends BaseVersion implements VersionContract, ConvertableVersion, PackageContract
{
    use ConvertsVersionImplementations, Package;

    /**
     * Package Vendor.
     *
     * @const string
     */
    public const VENDOR = 'CupOfTea';

    /**
     * Package Name.
     *
     * @const string
     */
    public const PACKAGE = 'SemVer';

    /**
     * Package Version.
     *
     * @const string
     */
    public const VERSION = '0.0.0';

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
    public static function create(int $major = 0, int $minor = 0, int $patch = 0, ?string $prerelease = null, ?string $build = null, bool $hasPrefix = true): Version
    {
        $version = parent::create($major, $minor, $patch, $prerelease, $build);
        $version->setPrefix($hasPrefix);

        return $version;
    }

    /**
     * Enable the 'v' prefix.
     *
     * @return $this
     */
    public function withPrefix(): Version
    {
        $this->prefix = true;

        return $this;
    }

    /**
     * Disable the 'v' prefix.
     *
     * @return $this
     */
    public function withoutPrefix(): Version
    {
        $this->prefix = false;

        return $this;
    }

    /**
     * Enable or disable the 'v' prefix.
     *
     * @param  bool  $prefix
     * @return void
     */
    public function setPrefix(bool $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Convert the Version instance to a PrefixedVersion instance.
     *
     * @return \CupOfTea\SemVer\PrefixedVersion
     */
    public function toPrefixed(): PrefixedVersion
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->convertTo(PrefixedVersion::class);
    }


    /**
     * Convert the Version instance to an UnprefixedVersion instance.
     *
     * @return \CupOfTea\SemVer\UnprefixedVersion
     */
    public function toUnprefixed(): UnprefixedVersion
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->convertTo(UnprefixedVersion::class);
    }

    /**
     * Get the version string.
     *
     * @param  bool  $ignorePrefix
     * @return string
     */
    public function getVersion(bool $ignorePrefix = false): string
    {
        return ($this->prefix && ! $ignorePrefix ? 'v' : '') . Versionable::getVersion();
    }

    /**
     * {@inheritDoc}
     */
    protected function handlePrefix(array $matches): void
    {
        $this->prefix = isset($matches['prefix']) && ! empty($matches['prefix']);
    }
}
