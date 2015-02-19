<?php

namespace WP_Domain_Work\WP\post\post_status;

/**
 * Pending state's texts (if 2nd string is exists, that is context)
 * 
 *
 * << PHP >>
 *
 * 'Pending', 'post'
 * - label of status
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L133
 *
 * 'Pending <span class="count">(%s)</span>'
 * - label count
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L136
 *
 * 'Pending Review'
 * - description of status (used by get_post_statuses)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L906
 * - submit meta box for display status & selectable option's label
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L82
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L104
 *
 * 'Save as Pending'
 * - submit meta box for saving as pending
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L33
 *
 * 'Submit for Review' ################# yet #################
 * - submit button label for no capability to publish post
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L257
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L258
 * 
 * 'Pending', 'post state'
 * - in posts list table (@edit.php) pending status post's sufix (used by _post_states)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/template.php#L1648
 * 
 *
 * << JavaScript >>
 * - json data 'postL10n' in admin page
 *
 * 'Save as Pending'
 * - key: 'savePending'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L454
 */
class pending {
	use built_in;

	protected $labels = [
		'label'       => '',
		'description' => '',
		'save_action' => '',
		'states'      => '',
	];

	protected static $defaults = [
		'label'       => [ 'Pending', 'post' ],
		'description' => 'Pending Review',
		'save_action' => 'Save as Pending',
		'states'      => [ 'Pending', 'post state' ],
	];

	protected function js_texts() {
		\WP_Domain_Work\WP\admin\js\L10n::set( 'postL10n', 'savePending', $this->labels['save_action'] );
	}

}
