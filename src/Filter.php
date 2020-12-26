<?php

namespace CupOfTea\SemVer;

class Filter
{
    /**
     * Create a filter callback to filter a set of versions allowing versions equal to the given version.
     *
     * @param  \CupOfTea\SemVer\Version|string  $version
     * @return callable
     */
    public static function eq($version): callable
    {
        return static function($vcomp) use ($version) {
            return Compare::eq($vcomp, $version);
        };
    }

    /**
     * Create a filter callback to filter a set of versions allowing versions greater than the given version.
     *
     * @param  \CupOfTea\SemVer\Version|string  $version
     * @return callable
     */
    public static function gt($version): callable
    {
        return static function($vcomp) use ($version) {
            return Compare::gt($vcomp, $version);
        };
    }

    /**
     * Create a filter callback to filter a set of versions allowing versions greater than or equal to the given version.
     *
     * @param  \CupOfTea\SemVer\Version|string  $version
     * @return callable
     */
    public static function gte($version): callable
    {
        return static function($vcomp) use ($version) {
            return Compare::gte($vcomp, $version);
        };
    }

    /**
     * Create a filter callback to filter a set of versions allowing versions less than the given version.
     *
     * @param  \CupOfTea\SemVer\Version|string  $version
     * @return callable
     */
    public static function lt($version): callable
    {
        return static function($vcomp) use ($version) {
            return Compare::lt($vcomp, $version);
        };
    }

    /**
     * Create a filter callback to filter a set of versions allowing versions less than or equal to the given version.
     *
     * @param  \CupOfTea\SemVer\Version|string  $version
     * @return callable
     */
    public static function lte($version): callable
    {
        return static function($vcomp) use ($version) {
            return Compare::lte($vcomp, $version);
        };
    }
}