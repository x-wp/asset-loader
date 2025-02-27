<?php
/**
 * Font class file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

namespace XWP\Dependency\Resources;

/**
 * Font resource.
 */
class Font extends File {
    /**
     * Add the preload tag to the head for the font.
     *
     * @param  int|null $priority Priority of the action. Defaults to the bundle priority.
     * @return bool
     */
    public function preload( ?int $priority = null ): bool {
        if ( \did_action( 'wp_head' ) ) {
            return false;
        }

        \add_action(
            'wp_head',
            function () {
				\printf(
                    '<link rel="preload" href="%s" as="font" type="font/%s" crossorigin>',
                    \esc_url( $this->uri() ),
                    \esc_attr( $this->ext() ),
				);
			},
            $priority ?? $this->bundle->priority(),
        );

        return true;
    }
}
