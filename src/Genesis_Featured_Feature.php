<?php
/**
 * Genesis Featured Feature Widget Class File
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\WP\Widgets
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2019 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\WP\Widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Genesis_Featured_Feature' ) ) {
	/**
	 * Class Genesis_Featured_Feature
	 *
	 * Genesis Featured Feature widget class.
	 *
	 * @package WPS\WP\Widgets
	 */
	class Genesis_Featured_Feature extends Genesis_Featured_Widget {

		/**
		 * Holds the Post Type for the featured Feature.
		 *
		 * @var string
		 */
		protected $post_type = 'feature';
	}
}
