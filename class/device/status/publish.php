<?php
namespace WPDW\Device\Status;

class publish {
	use built_in;

	/**
	 * Default labels for publish status
	 *
	 * @var array
	 */
	private static $defaults = [
		'name'   => '%s',
		'action' => '%s',
		'published_on' => '%s on:',
		'publish_on'   => '%s on:',
		'publish_immediately' => '%s <b>immediately</b>'
	];

	/**
	 * Publish status' texts
	 *
	 * @var array
	 */
	private $texts = [

		'Published' => [

			/**
			 * Label of status
			 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L112
			 */
			'post' => '{{name}}',

			/**
			 * Description of status (used by get_post_statuses)
			 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L908
			 *
			 * Submit meta box for display status & selectable option's label
			 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L76
			 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L98
			 */
			'{{name}}',

		],

		/**
		 * Publish box date format in submit meta box (for publish or private posts)
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L170
		 */
		'Published on: <b>%1$s</b>' => [
			'{{published_on}} <b>%1$s</b>',
		],

		/**
		 * Publish box date format for draft status post, (no date specified ???)
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L172
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L180
		 */
		'Publish <b>immediately</b>' => [
			'{{publish_immediately}}'
		],

		/**
		 * Publish box date format for draft status post, (date specified ???)
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L176
		 */
		'Publish on: <b>%1$s</b>' => [
			'{{publish_on}} <b>%1$s</b>'
		],

		/**
		 * Submit button label for publishing action
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L253
		 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L254
		 */
		'Publish' => [
			'{{action}}'
		],

	];

	/**
	 * JavaScript texts (postL10n)
	 *
	 * @var  array
	 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L446
	 */
	private $js_texts = [
		'publishOnPast' => 'published_on',
		'publish'   => 'action',
		'published' => 'name'
	];

}
