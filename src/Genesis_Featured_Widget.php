<?php
/**
 * Abstract Widget Genesis Featured Class File
 *
 * Genesis Featured Widget.
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

if ( ! class_exists( __NAMESPACE__ . '\Genesis_Featured_Widget' ) ) {
	/**
	 * WPS Featured "abstract" widget class.
	 *
	 * @package WPS\WP\Widgets
	 */
	abstract class Genesis_Featured_Widget extends WP_Widget {

		/**
		 * Holds the Post Type for the featured Feature.
		 *
		 * @var string
		 */
		protected $post_type = 'post';

		/**
		 * Holds widget settings defaults, populated in constructor.
		 *
		 * @var array
		 */
		protected $defaults;

		/**
		 * Genesis_Featured_Widget constructor.
		 */
		public function __construct() {

			$pt   = get_post_type_object( $this->post_type );
			$name = $pt->labels->name;

			$widget_ops = array(
				'classname'   => 'featured-content featuredpost featured-' . $this->post_type,
				'description' => __( 'Displays featured ' . $name . ' with thumbnails', 'wps' ),
			);

			$control_ops = array(
				'id_base' => 'featured-' . $this->post_type,
				'width'   => 505,
				'height'  => 350,
			);

			parent::__construct( 'featured-' . $this->post_type, __( 'Genesis - Featured ' . $name, 'wps' ), $widget_ops, $control_ops );

			add_filter( 'genesis_attr_entry', array( $this, 'genesis_attributes_entry' ), 10, 3 );

		}

		/**
		 * Default values.
		 *
		 * @return array Array of default values.
		 */
		protected function get_defaults() {
			return array(
				'title'             => '',
				'posts_num'         => '',
				'posts_offset'      => 0,
				'orderby'           => '',
				'order'             => '',
				'exclude_displayed' => 0,
				'exclude_sticky'    => 0,
				'show_image'        => 0,
				'image_alignment'   => '',
				'image_size'        => '',
				'show_title'        => 0,
				'post_info'         => '',
				'show_content'      => 'excerpt',
				'content_limit'     => '',
				'more_text'         => __( '[Read More...]', 'wps' ),
				'extra_num'         => '',
				'extra_title'       => '',
				'class'             => '',
				'taxonomy'          => '',
				'term'              => '',
			);
		}


		/**
		 * Echo the widget content.
		 *
		 * @global \WP_Query $wp_query               Query object.
		 * @global array     $_genesis_displayed_ids Array of displayed post IDs.
		 * @global int       $more
		 *
		 * @param array      $args                   Display arguments including `before_title`, `after_title`,
		 *                                           `before_widget`, and `after_widget`.
		 * @param array      $instance               The settings for the particular instance of the widget.
		 */
		public function widget( $args, $instance ) {

			global $_genesis_displayed_ids;

			// Merge with defaults.
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			echo $args['before_widget'];

			// Set up the author bio.
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
			}

			$query_args = array(
				'post_type'           => $this->post_type,
				'showposts'           => $instance['posts_num'],
				'offset'              => $instance['posts_offset'],
				'orderby'             => $instance['orderby'],
				'order'               => $instance['order'],
				'ignore_sticky_posts' => $instance['exclude_sticky'],
			);

			// Exclude displayed IDs from this loop?
			if ( $instance['exclude_displayed'] ) {
				$query_args['post__not_in'] = (array) $_genesis_displayed_ids;
			}

			if ( isset( $instance['taxonomy'] ) && '' !== $instance['taxonomy'] ) {
				$query_args['tax_query'] = array(
					'relation' => 'AND',
					array(
						'taxonomy'         => $instance['taxonomy'],
						'field'            => 'id',
						'terms'            => array( $instance['term'] ),
						'include_children' => false,
						'operator'         => 'IN',
					)
				);
			}

			$query = new \WP_Query( $query_args );
			add_filter( 'genesis_attr_entry', array( $this, 'genesis_attr_entry' ), 10, 3 );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();

					$_genesis_displayed_ids[] = get_the_ID();

					$this->do_entry( $args, $instance, $query->current_post );
				}
			}

			// Restore original query.
			wp_reset_query();

			echo $args['after_widget'];

		}

		/**
		 * @param array $args     Display arguments including `before_title`, `after_title`, `before_widget`, and `after_widget`.
		 * @param array $instance The settings for the particular instance of the widget.
		 * @param int   $count    Current post counter.
		 */
		public function do_entry( $args, $instance, $count ) {
			genesis_markup( array(
				'open'    => '<article %s>',
				'context' => 'entry',
				'params'  => array(
					'is_widget'    => true,
					'column_class' => $instance['class'],
					'count'        => $count,
				),
			) );

			if ( $instance['show_image'] ) {

				$image = genesis_get_image( array(
					'format'  => 'html',
					'size'    => $instance['image_size'],
					'context' => 'featured-post-widget',
					'attr'    => genesis_parse_attr( 'entry-image-widget', array( 'alt' => get_the_title() ) ),
				) );

				if ( $image ) {
					$role = empty( $instance['show_title'] ) ? '' : 'aria-hidden="true"';
					printf(
						'<a href="%s" class="%s" %s>%s</a>',
						get_permalink(),
						esc_attr( $instance['image_alignment'] ),
						$role,
						wp_make_content_images_responsive( $image )
					);
				}
			}

			if ( $instance['show_title'] ) {

				$header = '';

				if ( ! empty( $instance['show_title'] ) ) {

					$title = get_the_title() ? get_the_title() : __( '(no title)', 'wps' );

					/**
					 * Filter the featured post widget title.
					 *
					 * @since  2.2.0
					 *
					 * @param string $title                   Featured post title.
					 * @param array  $instance                {
					 *                                        Widget settings for this instance.
					 *
					 * @type string  $title                   Widget title.
					 * @type int     $posts_cat               ID of the post category.
					 * @type int     $posts_num               Number of posts to show.
					 * @type int     $posts_offset            Number of posts to skip when
					 *                                           retrieving.
					 * @type string  $orderby                 Field to order posts by.
					 * @type string  $order                   ASC fr ascending order, DESC for
					 *                                           descending order of posts.
					 * @type bool    $exclude_displayed       True if posts shown in main output
					 *                                           should be excluded from this widget
					 *                                           output.
					 * @type bool    $show_image              True if featured image should be
					 *                                           shown, false otherwise.
					 * @type string  $image_alignment         Image alignment: `alignnone`,
					 *                                           `alignleft`, `aligncenter` or `alignright`.
					 * @type string  $image_size              Name of the image size.
					 * @type bool    $show_gravatar           True if author avatar should be
					 *                                           shown, false otherwise.
					 * @type string  $gravatar_alignment      Author avatar alignment: `alignnone`,
					 *                                           `alignleft` or `aligncenter`.
					 * @type int     $gravatar_size           Dimension of the author avatar.
					 * @type bool    $show_title              True if featured page title should
					 *                                           be shown, false otherwise.
					 * @type bool    $show_byline             True if post info should be shown,
					 *                                           false otherwise.
					 * @type string  $post_info               Post info contents to show.
					 * @type bool    $show_content            True if featured page content
					 *                                           should be shown, false otherwise.
					 * @type int     $content_limit           Amount of content to show, in
					 *                                           characters.
					 * @type int     $more_text               Text to use for More link.
					 * @type int     $extra_num               Number of extra post titles to show.
					 * @type string  $extra_title             Heading for extra posts.
					 * @type bool    $more_from_category      True if showing category archive
					 *                                           link, false otherwise.
					 * @type string  $more_from_category_text Category archive link text.
					 * }
					 *
					 * @param array  $args                    {
					 *                                        Widget display arguments.
					 *
					 * @type string  $before_widget           Markup or content to display before the widget.
					 * @type string  $before_title            Markup or content to display before the widget title.
					 * @type string  $after_title             Markup or content to display after the widget title.
					 * @type string  $after_widget            Markup or content to display after the widget.
					 * }
					 */
					$title   = apply_filters( 'genesis_featured_post_title', $title, $instance, $args );
					$heading = genesis_a11y( 'headings' ) ? 'h4' : 'h2';

					$header .= genesis_markup( array(
						'open'    => "<{$heading} %s>",
						'close'   => "</{$heading}>",
						'context' => 'entry-title',
						'content' => sprintf( '<a href="%s">%s</a>', get_permalink(), $title ),
						'params'  => array(
							'is_widget' => true,
							'wrap'      => $heading,
						),
						'echo'    => false,
					) );

				}

				genesis_markup( array(
					'open'    => '<header %s>',
					'close'   => '</header>',
					'context' => 'entry-header',
					'params'  => array(
						'is_widget' => true,
					),
					'content' => $header,
				) );

			}

			if ( ! empty( $instance['show_content'] ) ) {

				genesis_markup( array(
					'open'    => '<div %s>',
					'context' => 'entry-content',
					'params'  => array(
						'is_widget' => true,
					),
				) );

				if ( 'excerpt' === $instance['show_content'] ) {
					the_excerpt();
				} elseif ( 'content-limit' === $instance['show_content'] ) {
					the_content_limit( (int) $instance['content_limit'], genesis_a11y_more_link( esc_html( $instance['more_text'] ) ) );
				} else {

					global $more;

					$orig_more = $more;
					$more      = 0;

					the_content( genesis_a11y_more_link( esc_html( $instance['more_text'] ) ) );

					$more = $orig_more;

				}

				genesis_markup( array(
					'close'   => '</div>',
					'context' => 'entry-content',
					'params'  => array(
						'is_widget' => true,
					),
				) );

			}

			genesis_markup( array(
				'close'   => '</article>',
				'context' => 'entry',
				'params'  => array(
					'is_widget' => true,
				),
			) );
		}

		/**
		 * Add attributes for widget entry wrapper elements.
		 *
		 * @param array  $attributes Existing attributes for footer widget area wrapper elements.
		 * @param string $context    Not used. Markup context (ie. `footer-widget-area`).
		 * @param array  $args       Markup arguments.
		 *
		 * @return array Amended attributes for footer widget area wrapper elements.
		 */
		public function genesis_attr_entry( $attributes, $context, $args ) {

			if (
				empty( $args['params'] ) ||
				(
					! empty( $args['params'] ) &&
					empty( $args['params']['is_widget'] ) ||
					(
						! empty( $args['params'] ) &&
						isset( $args['params']['is_widget'] ) &&
						! $args['params']['is_widget']
					)
				)
			) {
				return $attributes;
			}

			$attributes['class'] = $args['params']['column_class'] ? $args['params']['column_class'] . ' ' . $attributes['class'] : $attributes['class'];

			return $attributes;

		}

		/**
		 * Update a particular instance.
		 *
		 * This function should check that $new_instance is set correctly.
		 * The newly calculated value of $instance should be returned.
		 * If "false" is returned, the instance won't be saved/updated.
		 *
		 * @param array $new_instance New settings for this instance as input by the user via `form()`.
		 * @param array $old_instance Old settings for this instance.
		 *
		 * @return array Settings to save or bool false to cancel saving.
		 */
		public function update( $new_instance, $old_instance ) {

			$post_num                  = (int) $new_instance['posts_num'];
			$new_instance['posts_num'] = $post_num > 0 && $post_num < 100 ? $post_num : 1;
			$new_instance['title']     = strip_tags( $new_instance['title'] );
			$new_instance['more_text'] = strip_tags( $new_instance['more_text'] );

			return $new_instance;

		}

		/**
		 * Echo the settings update form.
		 *
		 * @param array $instance Current settings.
		 *
		 * @return void
		 */
		public function form( $instance ) {

			// Merge with defaults.
			$instance = wp_parse_args( (array) $instance, $this->defaults );
			$pt       = get_post_type_object( $this->post_type );
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
                        <input id="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>" type="checkbox"
                               onchange="wpsWidgetSave(this)"
                               name="<?php echo esc_attr( $this->get_field_name( 'show_title' ) ); ?>"
                               value="1" <?php checked( $instance['show_title'] ); ?>/>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>"><?php _e( 'Show Post Title', 'wps' ); ?></label>
                    </p>

                    <p>
                        <label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>"><?php _e( 'Content Type', 'wps' ); ?>
                            :</label>
                        <select id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>"
                                onchange="wpsWidgetSave(this)"
                                name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>">
							<?php if ( post_type_supports( $this->post_type, 'content' ) ) : ?>
                                <option value="content" <?php selected( 'content', $instance['show_content'] ); ?>><?php _e( 'Show Content', 'wps' ); ?></option>
							<?php endif; ?>
							<?php if ( post_type_supports( $this->post_type, 'content' ) ) : ?>
                                <option value="excerpt" <?php selected( 'excerpt', $instance['show_content'] ); ?>><?php _e( 'Show Excerpt', 'wps' ); ?></option>
							<?php endif; ?>
							<?php if ( post_type_supports( $this->post_type, 'content' ) ) : ?>
                                <option value="content-limit" <?php selected( 'content-limit', $instance['show_content'] ); ?>><?php _e( 'Show Content Limit', 'wps' ); ?></option>
							<?php endif; ?>
                            <option value="" <?php selected( '', $instance['show_content'] ); ?>><?php _e( 'No Content', 'wps' ); ?></option>
                        </select>
						<?php if ( post_type_supports( $this->post_type, 'content' ) && $instance['show_content'] === 'content-limit' ) : ?>
                            <br/>
                            <label for="<?php echo esc_attr( $this->get_field_id( 'content_limit' ) ); ?>"><?php _e( 'Limit content to', 'wps' ); ?>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->get_field_id( 'content_limit' ) ); ?>"
                                       name="<?php echo esc_attr( $this->get_field_name( 'content_limit' ) ); ?>"
                                       value="<?php echo esc_attr( (int) $instance['content_limit'] ); ?>" size="3"/>
								<?php _e( 'characters', 'wps' ); ?>
                            </label>
						<?php endif; ?>
                    </p>

					<?php if (
						( post_type_supports( $this->post_type, 'content' ) && $instance['show_content'] === 'content-limit' ) ||
						( post_type_supports( $this->post_type, 'excerpt' ) && $instance['show_content'] === 'excerpt' )
					) : ?>
                        <p>
                            <label for="<?php echo esc_attr( $this->get_field_id( 'more_text' ) ); ?>"><?php _e( 'More Text (if applicable)', 'wps' ); ?>
                                :</label>
                            <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'more_text' ) ); ?>"
                                   name="<?php echo esc_attr( $this->get_field_name( 'more_text' ) ); ?>"
                                   value="<?php echo esc_attr( $instance['more_text'] ); ?>"/>
                        </p>
					<?php endif; ?>
                </div>

				<?php if ( post_type_supports( $this->post_type, 'thumbnail' ) ) : ?>
                    <div class="genesis-widget-column-box">

                        <p>
                            <input id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" type="checkbox"
                                   onchange="wpsWidgetSave(this)"
                                   name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>"
                                   value="1" <?php checked( $instance['show_image'] ); ?>/>
                            <label for="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>"><?php _e( 'Show Featured Image', 'wps' ); ?></label>
                        </p>

                        <p>
                            <label for="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>"><?php _e( 'Image Size', 'wps' ); ?>
                                :</label>
                            <select id="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>"
                                    onchange="wpsWidgetSave(this)"
                                    class="genesis-image-size-selector"
                                    name="<?php echo esc_attr( $this->get_field_name( 'image_size' ) ); ?>">
								<?php
								$sizes = genesis_get_image_sizes();
								foreach ( (array) $sizes as $name => $size ) {
									printf( '<option value="%s" %s>%s (%sx%s)</option>', esc_attr( $name ), selected( $name, $instance['image_size'], false ), esc_html( $name ), esc_html( $size['width'] ), esc_html( $size['height'] ) );
								}
								?>
                            </select>
                        </p>

                        <p>
                            <label for="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>"><?php _e( 'Image Alignment', 'wps' ); ?>
                                :</label>
                            <select id="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>"
                                    onchange="wpsWidgetSave(this)"
                                    name="<?php echo esc_attr( $this->get_field_name( 'image_alignment' ) ); ?>">
                                <option value="alignnone">- <?php _e( 'None', 'wps' ); ?> -</option>
                                <option value="alignleft" <?php selected( 'alignleft', $instance['image_alignment'] ); ?>><?php _e( 'Left', 'wps' ); ?></option>
                                <option value="alignright" <?php selected( 'alignright', $instance['image_alignment'] ); ?>><?php _e( 'Right', 'wps' ); ?></option>
                                <option value="aligncenter" <?php selected( 'aligncenter', $instance['image_alignment'] ); ?>><?php _e( 'Center', 'wps' ); ?></option>
                            </select>
                        </p>

                    </div>
				<?php endif; ?>

                <div class="genesis-widget-column-box" style="margin-bottom: 10px;">

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

		/**
		 * Add attributes for entry element.
		 *
		 * @param array $attributes Existing attributes for entry element.
		 *
		 * @return array Amended attributes for entry element.
		 */
		public function genesis_attributes_entry( $attributes, $context, $args ) {
			$attributes['class'] = implode( ' ', get_post_class() );

			if (
				isset( $args['params'] ) &&
				isset( $args['params']['is_widget'] ) && $args['params']['is_widget'] &&
				isset( $args['params']['count'] ) &&
				isset( $args['params']['column_class'] )
			) {
				$attributes['class'] .= ' count-' . $args['params']['count'];
				$attributes['class'] .= ' ' . $this->get_column_classes( $args['params']['column_class'], $args['params']['count'] );
			}

			if ( ! is_main_query() && ! genesis_is_blog_template() ) {
				return $attributes;
			}

			$attributes['itemscope'] = true;
			$attributes['itemtype']  = 'https://schema.org/CreativeWork';

			return $attributes;

		}

		/**
		 * Column Classes
		 *
		 * @param int $columns       , how many columns content should be broken into
		 * @param int $count         , the current post in the loop (starts at 0)
		 * @param int $extra_classes , any additional classes to add on all posts
		 *
		 * @return string $classes
		 */
		public function get_column_classes( $column_class = '', $count = 0, $extra_classes = '' ) {
			$column_classes = array(
				'',
				'',
				'one-half',
				'one-third',
				'one-fourth',
				'one-fifth',
				'one-sixth',
			);


			$output  = $column_class;
			$columns = array_search( $column_class, $column_classes );
			if ( 0 == $count || 0 == $count % $columns ) {
				$output .= ' first';
			}
			if ( $extra_classes ) {
				$output .= ' ' . $extra_classes;
			}

			return $output;
		}

	}
}
