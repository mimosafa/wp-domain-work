<?php

namespace module;

trait status {
	use base;

	private static $postL10n_props = [];

	private static $builtin_statuses = [
		'publish', 'future', 'draft', 'pending', 'private', 'trash'
	];

	private function init() {
		if ( property_exists( $this, 'builtin' ) && is_array( $this->builtin ) && $this->builtin ) {
			$this->init_builtin_statuses();
		}
		if ( property_exists( $this, 'custom' ) && is_array( $this->custom ) && $this->custom ) {
			$this->init_custom_statuses();
		}
	}

	private function init_builtin_statuses() {
		foreach ( $this->builtin as $status => $args ) {
			if ( ! in_array( $status, self::$builtin_statuses ) ) {
				continue;
			}
			$class = sprintf( '\\wordpress\\admin\\post_status\\%s', $status );
			if ( class_exists( $class ) ) {
				new $class( $args );
			}
		}
	}

	private function init_custom_statuses() {
		#foreach ( $this->custom as $status => $args ) {
			register_post_status( 'withdrawal', array(
				'label'                     => '退会',
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( '退会 <span class="count">(%s)</span>', '退会 <span class="count">(%s)</span>' ),
			) );
			add_action( 'post_submitbox_misc_actions', function() {
				global $post;
?>
<script>
  jQuery(document).ready(function($){
    $("select#post_status").append("<option value=\"withdrawal\" <?php selected('withdrawal', $post->post_status); ?>>退会済み</option>");
  });
</script>
<?php
			} );
		#	register_post_status( $status, $args );
		#}
	}

}
