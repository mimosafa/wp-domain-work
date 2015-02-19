<?php

namespace WP_Domain_Work\WP\post\post_status;

/**
 * Publish state's texts (if 2nd string is exists, that is context)
 * 
 *
 * << PHP >>
 *
 * 'Published', 'post'
 * - label of status
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L112
 *
 * 'Published <span class="count">(%s)</span>'
 * - label count
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L115
 *
 * 'Published'
 * - description of status (used by get_post_statuses)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L908
 * - submit meta box for display status & selectable option's label
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L76
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L98
 *
 * 'Published on: <b>%1$s</b>'
 * - Publish box date format in submit meta box (for publish or private posts)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L170
 *
 * 'Publish <b>immediately</b>'
 * - Publish box date format for draft status post, (no date specified ???)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L172
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L180
 *
 * 'Publish on: <b>%1$s</b>'
 * - Publish box date format for draft status post, (date specified ???)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L176
 *
 * 'Publish'
 * - submit button label for publishing action
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L253
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L254
 * 
 *
 * << JavaScript >>
 * - json data 'postL10n' in admin page
 *
 * 'Published on:'
 * - key: 'publishOnPast'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L446
 *
 * 'Publish'
 * - key: 'publish'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L451
 * 
 * 'Published'
 * - key: 'published'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L461
 */
class publish {
	use built_in;

	private $labels = [
		'label'       => '',
		'description' => '',
		'save_action' => '',
	];

	private static $defaults = [
		'label'       => [ 'Published', 'post' ],
		'description' => 'Published',
		'save_action' => 'Publish',
	];

	protected function js_texts() {
		\WP_Domain_Work\WP\admin\js\L10n::set( 'postL10n', 'published', $this->labels['description'] );
	}

}
