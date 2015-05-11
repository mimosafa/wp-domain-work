<?php
namespace WPDW\Device\Status;

class pending {
	use built_in;

	/**
	 * Default labels for pending status
	 *
	 * @var array
	 */
	private static $defaults = [
		'name'        => '%s',
		'description' => '%s',
		'action'      => 'Save as %s',
	];

	/**
	 * Pending status' texts
	 *
	 * @var array
	 */
	private $texts = [

		'Pending' => [

			/**
			 * Label of status
			 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L133
			 */
			'post' => '{{name}}',

			/**
			 * Pending status post's sufix in posts list table (@edit.php)
			 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/template.php#L1648
			 */
			'post state' => '{{name}}',

		],

		/**
		 * Description of status (used by get_post_statuses)
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L906
		 *
		 * Submit meta box for display status & selectable option's label
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L82
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L104
		 */
		'Pending Review' => [
			'{{description}}',
		],

		/**
		 * Submit meta box for saving as pending
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L33
		 */
		'Save as Pending' => [
			'{{action}}'
		],

		/**
		 * Submit button label for no capability to publish post
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L257
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L258
		 */
		'Submit for Review' => [
			'{{action}}'
		],

	];

	/**
	 * JavaScript texts (postL10n)
	 *
	 * @var  array
	 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L454
	 */
	private $js_texts = [
		'savePending' => 'action',
	];

}
