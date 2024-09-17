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
     * @return bool
     */
    public function preload(): bool {
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
            $this->bundle->priority(),
        );

        return true;
    }
}
