<?php
namespace XWP\Dependency\Resources;

class File {
    /**
     * Asset handle
     *
     * @var string
     */
    public readonly string $handle;

    public function __construct(
        public readonly string $src,
        public readonly string $namespace,
    ) {
    }
}
