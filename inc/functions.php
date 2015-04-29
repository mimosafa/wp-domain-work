<?php
namespace WPDW;

/**
 * @access private
 * 
 * @param  string $domain
 * @return WP_Domain\{$domain}\property
 */
function _get_property( $domain ) {
	if ( ! $domain = filter_var( $domain ) )
		return null;
	$class = 'WP_Domain\\' . $domain . '\\property';
	return class_exists( $class ) ? $class::getInstance() : null;
}

/**
 * @access private
 *
 * @uses   WPDW\Options
 *
 * @param  string $alias
 * @return string
 */
function _domain( $alias ) {
	if ( ! $alias = filter_var( $alias ) )
		return '';
	static $domains = [];
	if ( ! $domains )
		$domains = Options::get_domains_alias();
	return isset( $domains[$alias] ) ? $domains[$alias] : '';
}

/**
 * @access private
 *
 * @uses   WPDW\Options
 *
 * @param  string $domain
 * @return string
 */
function _alias( $domain ) {
	if ( ! $domain = filter_var( $domain ) )
		return '';
	static $aliases = [];
	if ( ! $aliases )
		$aliases = array_flip( Options::get_domains_alias() );
	return isset( $aliases[$domain] ) ? $aliases[$domain] : '';
}
