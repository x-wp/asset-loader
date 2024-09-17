<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing

namespace XWP\Dependency\Resources;

/**
 * Style asset class.
 */
class Style extends Asset {
    protected function type(): string {
        return 'style';
    }

    protected function register_args(): array {
        return \array_merge(
            array( 'media' => 'all' ),
            parent::register_args(),
		);
    }

    /**
     * Styles can't be localized.
     *
     * @return array<string, mixed>|false
     */
    protected function localize_args(): array|bool {
        return false;
    }
}
