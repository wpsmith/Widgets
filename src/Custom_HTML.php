<?php
/**
 * Custom HTML Widget Class File
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

if ( ! class_exists( __NAMESPACE__ . '\Custom_HTML' ) ) {
	/**
	 * Class Custom_HTML
	 *
	 * @package WPS\WP\Widgets
	 */
	class Custom_HTML extends \WP_Widget_Custom_HTML {
		/**
		 * Custom_HTML constructor.
		 *
		 * Sets up a new Custom HTML widget instance.
		 */
		public function __construct() {
			unregister_widget( 'custom_html' );

			$widget_ops  = array(
				'classname'                   => 'widget_custom_html',
				'description'                 => __( 'Arbitrary HTML code.' ),
				'customize_selective_refresh' => true,
			);
			$control_ops = array(
				'width'  => 400,
				'height' => 350,
			);
			parent::__construct( 'custom_html', __( 'WPS Custom HTML' ), $widget_ops, $control_ops );

		}

		/**
		 * Outputs the content for the current Custom HTML widget instance.
		 *
		 * @global WP_Post $post
		 *
		 * @param array $args Display arguments including 'before_title', 'after_title',
		 *                        'before_widget', and 'after_widget'.
		 * @param array $instance Settings for the current Custom HTML widget instance.
		 */
		public function widget( $args, $instance ) {
			global $post;

			// Override global $post so filters (and shortcodes) apply in a consistent context.
			$original_post = $post;
			if ( is_singular() ) {
				// Make sure post is always the queried object on singular queries (not from another sub-query that failed to clean up the global $post).
				$post = get_queried_object();
			} else {
				// Nullify the $post global during widget rendering to prevent shortcodes from running with the unexpected context on archive queries.
				$post = null;
			}

			// Prevent dumping out all attachments from the media library.
			add_filter( 'shortcode_atts_gallery', array( $this, '_filter_gallery_shortcode_attrs' ) );

			$instance = array_merge( $this->default_instance, $instance );

			/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

			// Prepare instance data that looks like a normal Text widget.
			$simulated_text_widget_instance = array_merge( $instance, array(
				'text'   => isset( $instance['content'] ) ? $instance['content'] : '',
				'filter' => false, // Because wpautop is not applied.
				'visual' => false, // Because it wasn't created in TinyMCE.
			) );
			unset( $simulated_text_widget_instance['content'] ); // Was moved to 'text' prop.

			/** This filter is documented in wp-includes/widgets/class-wp-widget-text.php */
			$content = apply_filters( 'widget_text', $instance['content'], $simulated_text_widget_instance, $this );

			/**
			 * Filters the content of the Custom HTML widget.
			 *
			 * @since 4.8.1
			 *
			 * @param string $content The widget content.
			 * @param array $instance Array of settings for the current widget.
			 * @param \WP_Widget_Custom_HTML $this Current Custom HTML widget instance.
			 */
			$content = apply_filters( 'widget_custom_html_content', $content, $instance, $this );

			// Restore post global.
			$post = $original_post;
			remove_filter( 'shortcode_atts_gallery', array( $this, '_filter_gallery_shortcode_attrs' ) );

			// Inject the Text widget's container class name alongside this widget's class name for theme styling compatibility.
			$args['before_widget'] = preg_replace( '/(?<=\sclass=["\'])/', 'widget_text ', $args['before_widget'] );

			echo $args['before_widget'];
			if ( ! empty( $title ) && '' === $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			echo '<div class="textwidget custom-html-widget">'; // The textwidget class is for theme styling compatibility.
			echo $content;
			echo '</div>';
			echo $args['after_widget'];
		}
	}
}
