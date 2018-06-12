<?php
/**
 * Genesis Featured Van Widget Class File
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Widgets
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Widgets\Genesis_Featured_Van' ) ) {
	/**
	 * Class Genesis_Featured_Van
	 *
	 * Genesis Featured Van widget class.
	 *
	 * @package WPS\Widgets
	 */
	class Genesis_Featured_Van extends Genesis_Featured_Widget {

		/**
		 * Holds the Post Type for the featured Feature.
		 *
		 * @var string
		 */
		protected $post_type = 'van';
	}
}
