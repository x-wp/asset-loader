<?php
namespace XWP\Asset\Enums;

enum Cache {
    /**
     * Disables bundle caching.
     */
    case None;

    /**
     * Automatically determine the best cache method.
     */
    case Auto;

    /**
     * Caches the bundle in a transient.
     *
     * Transient name is in the format `loader_bundle_{namespace}`.
     */
    case Transient;

    /**
     * Caches the bundle as a JSON string.
     *
     * JSON file is saved in the `wp-content/cache/xwp-assets` directory.
     */
    case Json;
    case Object;
    case APCu;
    case File;
    case Compile;
    case Custom;
}
