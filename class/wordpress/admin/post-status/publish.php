<?php

namespace wordpress\admin\post_status;

/**
 *
 */
class publish {

	private $labels;

	private static $defaults = [
		'label'        => 'Publish',
		'display'      => 'Published',
		'label_counts' => '',
		'timestamp'    => 'Published on',
		#'save-action'  => '',
	];

	private $postL10nData = [];

	public function __construct( Array $args ) {
		$this->labels = wp_parse_args( $args, self::$defaults );
		$this->init();
	}

	private function init() {
		if ( ! $this->labels['display'] && $this->labels['label'] !== self::$defaults['label'] ) {
			$this->labels['display'] = $this->labels['label'];
		}
		if ( ! $this->labels['label_counts'] && $this->labels['display'] !== self::$defaults['display'] ) {
			$this->labels['label_counts'] = $this->labels['display'];
		}
		$this->status_labels();
		if ( $this->postL10nData ) {
			add_action( 'admin_footer-post.php', [ $this, 'js_postL10n' ], 99 );
		}
	}

	private function status_labels() {
		if ( ( $display = $this->labels['display'] ) && is_string( $display ) && $display !== self::$defaults['display'] ) {
			\wordpress\gettext::set( self::$defaults['display'], $display );
			$this->postL10nData['published'] = wp_json_encode( $this->labels['display'] );
		}
		if ( ( $lc = $this->labels['label_counts'] ) && is_string( $this->labels['label_counts'] ) ) {
			global $wp_post_statuses;
			$lc = $this->labels['label_counts'];
			$wp_post_statuses['publish']->label_count = _n_noop(
				$lc . ' <span class="count">(%s)</span>',
				$lc . ' <span class="count">(%s)</span>'
			);
		}
		if ( ( $timestamp = $this->labels['timestamp'] ) && is_string( $timestamp ) && $timestamp !== self::$defaults['timestamp'] ) {
			\wordpress\gettext::set( 'Published on: <b>%1$s</b>', $timestamp . ': <b>%1$s</b>' );
			$this->postL10nData['publishOnPast'] = wp_json_encode( $this->labels['timestamp'] );
		}
	}

	public function js_postL10n() {
		echo "<script type='text/javascript'>\n";
		foreach ( $this->postL10nData as $before => $after ) {
			echo <<<EOF
  postL10n.{$before} = {$after};\n
EOF;
		}
		echo "</script>\n";
	}

}
