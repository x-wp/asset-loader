<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing

namespace XWP\Dependency\Resources;

/**
 * Script asset class.
 */
class Script extends Asset {
    protected function type(): string {
        return 'script';
    }

    protected function register_args(): array {
        return \array_merge(
            array(
                'args' => array(
                    'in_footer' => true,
                ),
            ),
            parent::register_args(),
        );
    }

    /**
     * Aditionally processes array data - converts it to JS variables.
     *
     * @return array<string, mixed>|false
     */
    protected function add_inline_args(): array|bool {
        $args = parent::add_inline_args();

        if ( false === $args ) {
            return false;
        }

        if ( \is_array( $args['data'] ) ) {
            $args['data'] = \array_map(
                static fn( $v, $k ) => \sprintf( 'var %s = %s;', $k, \wp_json_encode( $v ) ),
                $args['data'],
                \array_keys( $args['data'] ),
            );
            $args['data'] = \implode( "\n", $args['data'] );
        }

        return $args;
    }

    public function localize(): bool {
        if ( \has_action( $this->action_name() ) ) {
            \do_action( $this->action_name(), $this );
            return true;
        }

        return parent::localize();
    }

    /**
     * Get the name of the legacy action for localizing the script.
     *
     * @return string
     */
    protected function action_name(): string {
        return "{$this->bundle->id()}_localize_{$this->type()}";
    }
}
