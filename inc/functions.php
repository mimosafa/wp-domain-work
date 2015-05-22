<?php
namespace WPDW;

/**
 * Find whether the string is 'domain'
 *
 * @access private
 *
 * @uses   WPDW\Domain
 * @see    wp-domain-work/class/domains.php
 *
 * @param  string $domain
 * @return boolean
 */
function _is_domain( $domain ) {
	return Domain::_is_domain( $domain );
}

/**
 * Find whether the post_type|taxonomy name is 'domain'
 *
 * @access private
 *
 * @uses   WPDW\Domain
 * @see    wp-domain-work/class/domains.php
 *
 * @param  string $alias Post type OR taxonomy name
 * @return boolean
 */
function _is_alias( $alias ) {
	return Domain::_is_alias( $alias );
}

/**
 * Get post_type|taxonomy name from domain name
 *
 * @access private
 *
 * @uses   WPDW\Domain
 * @see    wp-domain-work/class/domains.php
 *
 * @param  string $domain
 * @return string If supplied string is not domain, return empty string
 */
function _alias( $domain ) {
	return Domain::_alias( $domain );
}

/**
 * Get domain name from post_type|taxonomy name
 *
 * @access private
 *
 * @uses   WPDW\Domain
 * @see    wp-domain-work/class/domains.php
 *
 * @param  string $alias
 * @return string If supplied string is not domain alias, return empty string
 */
function _domain( $alias ) {
	return Domain::_domain( $alias );
}

/**
 * Get WP_Domain\{$domain}\property object
 * 
 * @access private
 * 
 * @param  string $domain
 * @return WP_Domain\{$domain}\property
 */
function _property( $domain ) {
	if ( ! $domain = filter_var( $domain ) )
		return null;
	$class = 'WP_Domain\\' . $domain . '\\property';
	return class_exists( $class ) ? $class::getInstance() : null;
}
