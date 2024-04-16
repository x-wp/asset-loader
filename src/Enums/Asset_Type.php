<?php //phpcs:disable PHPCompatibility.Variables.ForbiddenThisUseContexts.OutsideObjectContext
namespace XWP\Dependency\Enums;

enum Asset_Type {
    case Stylesheet;
    case Script;
    case Image;
    case Font;
    case File;

    /**
     * Get the extensions for the resource type.
     *
     * @return array<string>
     */
    public function extensions(): array {
        return match ( $this ) {
            self::Stylesheet => array( 'css' ),
            self::Script => array( 'js' ),
            self::Image => array( 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'avif', 'webp', 'bmp', 'svg' ),
            self::Font => array( 'woff', 'woff2', 'ttf', 'otf', 'eot' ),
            self::File => array( '*' ),
        };
    }
}
