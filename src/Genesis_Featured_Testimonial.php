<?php
/**
 * Genesis Featured Testimonial Widget Class File
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

if ( ! class_exists( __NAMESPACE__ . '\Genesis_Featured_Testimonial' ) ) {
	/**
	 * Class Genesis_Featured_Testimonial
	 *
	 * Genesis Featured Testimonial widget class.
	 *
	 * @package WPS\WP\Widgets
	 */
	class Genesis_Featured_Testimonial extends Genesis_Featured_Widget {

		/**
		 * Holds the Post Type for the featured Feature.
		 *
		 * @var string
		 */
		protected $post_type = 'testimonial';

		public function do_entry1( $args, $instance, $count ) {
			// ENTRY OPEN
			genesis_markup( array(
				'open'    => '<article %s>',
				'context' => 'entry',
				'params'  => array(
					'is_widget'    => true,
					'column_class' => $instance['class'],
				),
			) );

			// IMAGE
			$image = genesis_get_image( array(
				'format'  => 'html',
				'size'    => $instance['image_size'],
				'context' => 'featured-post-widget',
				'attr'    => genesis_parse_attr( 'entry-image-widget', array( 'alt' => get_the_title() ) ),
			) );

			if ( $image ) {
				printf( '<div class="first one-half">' );
				$role = empty( $instance['show_title'] ) ? '' : 'aria-hidden="true"';
				genesis_markup( array(
					'open'    => '<header %s>',
					'close'   => '</header>',
					'context' => 'entry-header',
					'params'  => array(
						'is_widget' => true,
					),
					'content' => sprintf( '<a href="%s" class="%s" %s>%s</a>', get_permalink(), esc_attr( $instance['image_alignment'] ), $role, wp_make_content_images_responsive( $image ) ),
				) );
				echo '</div>';
			}

			// CONTENT
			if ( ! $image ) {
				printf( '<div class="first full-width">' );
			} else {
				printf( '<div class="one-half">' );
			}
			genesis_markup( array(
				'open'    => '<blockquote %s>',
				'context' => 'entry-content',
				'params'  => array(
					'is_widget' => true,
				),
			) );

			the_excerpt();

			// TITLE
			$content = '';
			$title   = get_the_title() ? get_the_title() : __( '(no title)', 'wps' );

			$title   = apply_filters( 'genesis_featured_post_title', $title, $instance, $args );
			$content .= genesis_markup( array(
				'open'    => "<cite %s>",
				'close'   => "</cite>",
				'context' => 'entry-title',
				'content' => sprintf( '<a href="%s">%s</a>', get_permalink(), $title ),
				'params'  => array(
					'is_widget' => true,
					'wrap'      => 'cite',
				),
				'echo'    => false,
			) );

			genesis_markup( array(
				'open'    => '<footer %s>',
				'close'   => '</footer>',
				'context' => 'entry-footer',
				'params'  => array(
					'is_widget' => true,
				),
				'content' => $content,
			) );

			genesis_markup( array(
				'close'   => '</blockquote>',
				'context' => 'entry-content',
				'params'  => array(
					'is_widget' => true,
				),
			) );


			printf(
				'<a class="button button-white text-bold text-primary" href="%s">%s</a>',
				get_post_type_archive_link( $this->post_type ),
				__( 'More Stories', 'wps' )
			);

			echo '</div>';

			// ENTRY CLOSE
			genesis_markup( array(
				'close'   => '</article>',
				'context' => 'entry',
				'params'  => array(
					'is_widget' => true,
				),
			) );
		}

		/**
		 * Echo the settings update form.
		 *
		 * @since 0.1.8
		 *
		 * @param array $instance Current settings.
		 *
		 * @return void
		 */
		public function form1( $instance ) {

			// Merge with defaults.
			$instance = wp_parse_args( (array) $instance, $this->defaults );
			?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wps' ); ?>
                    :</label>
                <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                       value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
            </p>

            <div class="genesis-widget-column">

                <div class="genesis-widget-column-box genesis-widget-column-box-top">
					<?php
					$taxonomies = get_object_taxonomies( $this->post_type, 'objects' );
					if ( count( $taxonomies ) > 0 ) :
						?>
                        <p>
                            <label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php _e( 'Taxonomy', 'wps' ); ?>
                                :</label>
                            <select id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"
                                    onchange="wpsWidgetSave(this)"
                                    name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>">
                                <option value="">- <?php _e( 'None', 'wps' ); ?> -</option>
								<?php
								foreach ( (array) $taxonomies as $taxonomy ) {
									printf( '<option value="%s" %s>%s</option>', esc_attr( $taxonomy->name ), selected( $taxonomy->name, $instance['taxonomy'], false ), esc_html( $taxonomy->label ) );
								}
								?>
                            </select>
                        </p>
						<?php
						$terms = get_terms( $instance['taxonomy'], array(
							'hide_empty' => false,
						) );

						if ( $instance['taxonomy'] ) :
							?>
                            <p>
                                <label for="<?php echo esc_attr( $this->get_field_id( 'term' ) ); ?>"><?php _e( 'Term', 'wps' ); ?>
                                    :</label>
                                <select id="<?php echo esc_attr( $this->get_field_id( 'term' ) ); ?>"
                                        onchange="wpsWidgetSave(this)"
                                        name="<?php echo esc_attr( $this->get_field_name( 'term' ) ); ?>">
                                    <option value="">- <?php _e( 'None', 'wps' ); ?> -</option>
									<?php
									foreach ( (array) $terms as $term ) {
										printf( '<option value="%s" %s>%s</option>', esc_attr( $term->term_id ), selected( $term->term_id, $instance['term'], false ), esc_html( $term->name ) );
									}
									?>
                                </select>
                            </p>
						<?php
						endif;
					endif;
					?>
                    <p>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'posts_num' ) ); ?>"><?php _e( 'Number of Posts to Show', 'wps' ); ?>
                            :</label>
                        <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'posts_num' ) ); ?>"
                               name="<?php echo esc_attr( $this->get_field_name( 'posts_num' ) ); ?>"
                               value="<?php echo esc_attr( $instance['posts_num'] ); ?>" size="2" placeholder="1"/>
                    </p>

                    <p>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'posts_offset' ) ); ?>"><?php _e( 'Number of Posts to Offset', 'wps' ); ?>
                            :</label>
                        <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'posts_offset' ) ); ?>"
                               name="<?php echo esc_attr( $this->get_field_name( 'posts_offset' ) ); ?>"
                               value="<?php echo esc_attr( $instance['posts_offset'] ); ?>" size="2"/>
                    </p>

                    <p>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php _e( 'Order By', 'wps' ); ?>
                            :</label>
                        <select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"
                                onchange="wpsWidgetSave(this)"
                                name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
                            <option value="date" <?php selected( 'date', $instance['orderby'] ); ?>><?php _e( 'Date', 'wps' ); ?></option>
                            <option value="title" <?php selected( 'title', $instance['orderby'] ); ?>><?php _e( 'Title', 'wps' ); ?></option>
                            <option value="parent" <?php selected( 'parent', $instance['orderby'] ); ?>><?php _e( 'Parent', 'wps' ); ?></option>
                            <option value="ID" <?php selected( 'ID', $instance['orderby'] ); ?>><?php _e( 'ID', 'wps' ); ?></option>
                            <option value="comment_count" <?php selected( 'comment_count', $instance['orderby'] ); ?>><?php _e( 'Comment Count', 'wps' ); ?></option>
                            <option value="rand" <?php selected( 'rand', $instance['orderby'] ); ?>><?php _e( 'Random', 'wps' ); ?></option>
                        </select>
                    </p>

                    <p>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php _e( 'Sort Order', 'wps' ); ?>
                            :</label>
                        <select id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"
                                onchange="wpsWidgetSave(this)"
                                name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
                            <option value="DESC" <?php selected( 'DESC', $instance['order'] ); ?>><?php _e( 'Descending (3, 2, 1)', 'wps' ); ?></option>
                            <option value="ASC" <?php selected( 'ASC', $instance['order'] ); ?>><?php _e( 'Ascending (1, 2, 3)', 'wps' ); ?></option>
                        </select>
                    </p>

                    <p>
                        <input id="<?php echo esc_attr( $this->get_field_id( 'exclude_displayed' ) ); ?>"
                               type="checkbox"
                               onchange="wpsWidgetSave(this)"
                               name="<?php echo esc_attr( $this->get_field_name( 'exclude_displayed' ) ); ?>"
                               value="1" <?php checked( $instance['exclude_displayed'] ); ?>/>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'exclude_displayed' ) ); ?>"><?php _e( 'Exclude Previously Displayed Posts?', 'wps' ); ?></label>
                    </p>

                    <p>
                        <input id="<?php echo esc_attr( $this->get_field_id( 'exclude_sticky' ) ); ?>" type="checkbox"
                               onchange="wpsWidgetSave(this)"
                               name="<?php echo esc_attr( $this->get_field_name( 'exclude_sticky' ) ); ?>"
                               value="1" <?php checked( $instance['exclude_sticky'] ); ?>/>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'exclude_sticky' ) ); ?>"><?php _e( 'Exclude Sticky Posts?', 'wps' ); ?></label>
                    </p>

                </div>

            </div>

            <div class="genesis-widget-column genesis-widget-column-right">

                <div class="genesis-widget-column-box genesis-widget-column-box-top">

                    <p>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>"><?php _e( 'Column Class', 'wps' ); ?>
                            :</label>
                        <select id="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>" style="max-width: 100%;"
                                onchange="wpsWidgetSave(this)"
                                name="<?php echo esc_attr( $this->get_field_name( 'class' ) ); ?>">
                            <option value="">- <?php _e( 'None', 'wps' ); ?> -</option>
							<?php
							foreach (
								array(
									'one-half'      => __( 'One-Half (Two-Fourths, Three-Sixths)', 'wps' ),
									'one-third'     => __( 'One-Third (Two-Sixths)', 'wps' ),
									'one-fourth'    => __( 'One-Fourth', 'wps' ),
									'one-sixth'     => __( 'One-Sixth', 'wps' ),
									'two-thirds'    => __( 'Four-Sixths', 'wps' ),
									'three-fourths' => __( 'Three-Fourths', 'wps' ),
									'five-sixths'   => __( 'Five-Sixths', 'wps' ),
								) as $value => $class
							) {
								printf(
									'<option value="%s" %s>%s</option>',
									$value,
									selected( $value, $instance['class'] ),
									$class
								);
							}
							?>
                        </select>
                    </p>

                </div>

            </div>
			<?php

		}
	}
}
