<?php

namespace XWP\Dependency\Resources;

class Script extends Asset {
    protected function type(): string {
        return 'script';
    }

    protected function default_args(): array {
        return array(
			'args' => array(
				'in_footer' => true,
			),
		);
    }

    public function register(): bool {
        return parent::register() && $this->localize();
    }

    /**
     * Localize the script.
     *
     * @return bool
     */
    protected function localize(): bool {
        match ( true ) {
            \has_action( $this->action_name() ) => $this->legacy_localize(),
            \has_filter( $this->filter_name() ) => $this->modern_localize(),
            default                             => null,
        };

        return true;
    }

    /**
     * Localize the script using the legacy method.
     *
     * @return void
     */
    protected function legacy_localize(): void {
        \do_action( $this->action_name(), $this );
    }

    /**
     * Localize the script using the modern method.
     *
     * @return void
     */
    protected function modern_localize(): void {
        $args = array(
            'handle'      => $this->handle(),
            'l10n'        => array(),
            'object_name' => $this->name(),
        );

        $args = \apply_filters( "localize_params_{$this->handle()}", $args, $this );

        \wp_localize_script( ...$args );
    }

    /**
     * Get the name of the modern filter for localizing the script.
     *
     * @return string
     */
    protected function filter_name(): string {
        return "localize_params_{$this->handle()}";
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
