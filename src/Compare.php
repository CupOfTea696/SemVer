<?php

namespace CupOfTea\SemVer;

use CupOfTea\SemVer\Contracts\Version as VersionContract;

/**
 * The Compare class is used to compare two Versions.
 *
 * The pre-release version components are compared using `strnatcmp()`.
 * If the build version components are the only ones that differ, the versions
 * are considered equal, because comparing build strings is meaningless.
 */
class Compare
{
    /**
     * Check if the two given versions are equal.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v1
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v2
     * @return bool
     */
    public static function eq($v1, $v2): bool
    {
        [$v1, $v2] = self::cast($v1, $v2);
        $vc1 = clone $v1;
        $vc2 = clone $v2;

        $vc1->setBuild(null);
        $vc2->setBuild(null);

        return $vc1->getVersion() === $vc2->getVersion();
    }

    /**
     * Check if a given version is greater than another.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v1
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v2
     * @return bool
     */
    public static function gt($v1, $v2): bool
    {
        [$v1, $v2] = self::cast($v1, $v2);

        if (static::eq($v1, $v2)) {
            return false;
        }

        $maj1 = $v1->getMajor();
        $maj2 = $v2->getMajor();

        if ($maj1 !== $maj2) {
            return $maj1 > $maj2;
        }

        $min1 = $v1->getMinor();
        $min2 = $v2->getMinor();

        if ($min1 !== $min2) {
            return $min1 > $min2;
        }

        $p1 = $v1->getPatch();
        $p2 = $v2->getPatch();

        if ($p1 !== $p2) {
            return $p1 > $p2;
        }

        $ispre1 = $v1->isPrerelease();
        $ispre2 = $v2->isPrerelease();

        // If neither are pre-releases, v1 is not greater than v2.
        if (! $ispre1 && ! $ispre2) {
            return false;
        }

        // if either v1 or v2 is a pre-release, the one that isn't a pre-release is a greater version.
        if ($ispre1 ^ $ispre2) {
            return ! $ispre1;
        }

        return strnatcmp($v1->getPrerelease(), $v2->getPrerelease()) > 0;
    }

    /**
     * Check if a given version is greater than or equal to another.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v1
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v2
     * @return bool
     */
    public static function gte($v1, $v2): bool
    {
        if (static::eq($v1, $v2)) {
            return true;
        }

        return static::gt($v1, $v2);
    }

    /**
     * Check if a given version is greater than another.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v1
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v2
     * @return bool
     */
    public static function lt($v1, $v2): bool
    {
        return static::gt($v2, $v1);
    }

    /**
     * Check if a given version is less than or equal to another.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v1
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v2
     * @return bool
     */
    public static function lte($v1, $v2): bool
    {
        return static::gte($v2, $v1);
    }

    /**
     * Compare two versions using the spaceship comparison operator, handy for usort.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v1
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v2
     * @return int
     */
    public static function comp($v1, $v2): int
    {
        if (static::eq($v1, $v2)) {
            return 0;
        }

        return static::gt($v1, $v2) ? 1 : -1;
    }

    /**
     * Cast the given versions to a Version instance if they aren't already.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v1
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $v2
     * @return \CupOfTea\SemVer\Contracts\Version[]
     */
    protected static function cast($v1, $v2): array
    {
        if (! $v1 instanceof VersionContract) {
            $v1 = new Version($v1);
        }

        if (! $v2 instanceof VersionContract) {
            $v2 = new Version($v2);
        }

        return [$v1, $v2];
    }
}