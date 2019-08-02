<?php
/**
 * Meta Boxes setup
 */
if ( ! class_exists( 'WP_Meta_Fields' ) ) {

	/**
	 * Meta Boxes setup
	 */
	class WP_Meta_Fields {

		/**
		 * Instance
		 *
		 * @var $instance
		 */
		private static $instance;

		/**
		 * Meta Option
		 *
		 * @var $meta_boxes
		 */
		private $meta_boxes = array();

		/**
		 * Unique Keys
		 *
		 * @var $unique_keys
		 */
		private $unique_keys = array();

		/**
		 * Duplicate Keys
		 *
		 * @var $duplicate_keys
		 */
		private $duplicate_keys = array();

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

			// Shortcode.
			add_shortcode( 'mf', array( $this, 'shortcode_markup_meta' ) );
		}

		function shortcode_markup_meta( $atts = array(), $content = '' ) {
			$atts = shortcode_atts( array(
				'meta_key' => '',
				'post_id'  => '',
			), $atts );

			if( empty( $atts['meta_key'] ) ) {
				return '';
			}
		
			return $this->meta( $atts['meta_key'], $atts['post_id'] );
		}

		/**
		 * Enqueue Scripts
		 *
		 * @since 1.0.0
		 *
		 * @param  string $hook Current Hook.
		 * @return mixed
		 */
		function enqueue_scripts( $hook = '' ) {

			if( empty( $this->meta_boxes ) ) {
				return;
			}

			wp_enqueue_script( 'wp-post-meta-fields', $this->get_uri( __FILE__ ) . 'wp-meta-fields.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_style( 'wp-post-meta-fields', $this->get_uri( __FILE__ ) . 'wp-meta-fields.css', null, '1.0.0', 'all' );

			$css = '';
			foreach (wp_list_pluck( $this->meta_boxes, 'id' ) as $key => $meta_box_id) {
				$css .= '#'.$meta_box_id . ' .inside { margin: 0; padding: 0; }';
			}

			wp_add_inline_style( 'wp-post-meta-fields', $css );
		}

		/**
		 * Get current URL from the theme or plugin
		 *
		 * @since 1.0.0
		 *
		 * @param  string $file File Absolute Path.
		 * @return mixed
		 */
		function get_uri( $file = __FILE__ ) {
			$realpath   = realpath( dirname( $file ) );
			$path       = wp_normalize_path( $realpath );
			$theme_dir  = wp_normalize_path( get_template_directory() );
			$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );

			if ( strpos( $path, $theme_dir ) !== false ) {
				$current_dir = str_replace( $theme_dir, '', $path );
				return rtrim( get_template_directory_uri() ) . $current_dir . '/';
			} elseif ( strpos( $path, $plugin_dir ) !== false ) {
				return rtrim( plugin_dir_url( $file ) );
			}

			return;
		}

		/**
		 *  Init Metabox
		 */
		public function init_metabox() {
			add_action( 'add_meta_boxes', array( $this, 'setup_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_meta_box' ) );
		}

		/**
		 * Metabox Save
		 *
		 * @param  number $post_id Post ID.
		 * @return void
		 */
		function save_meta_box( $post_id ) {

			if( empty( $this->meta_boxes ) ) {
				return;
			}

			// Checks save status.
			$is_autosave    = wp_is_post_autosave( $post_id );
			$is_revision    = wp_is_post_revision( $post_id );
			$is_valid_nonce = ( isset( $_POST['wp_meta_fields_nonce'] ) && wp_verify_nonce( $_POST['wp_meta_fields_nonce'], basename( __FILE__ ) ) ) ? true : false;

			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
				return;
			}

			$all_fields_by_type = array();
			$all_fields = array();
			foreach ($this->meta_boxes as $key => $meta_box) {
				if( isset( $meta_box['fields'] ) ) {

					foreach ($meta_box['fields'] as $field_id => $field) {
						$all_fields_by_type[ $field['type'] ][] = $field_id;
					}
					$current_fields = array_keys( $meta_box['fields'] );
					$all_fields = array_merge($all_fields, $current_fields );
				} else if( isset( $meta_box['groups'] ) ) {
					if( ! empty( $meta_box['groups'] ) ) {
						foreach ($meta_box['groups'] as $key => $group_meta_box) {
							$current_fields = array_keys( $group_meta_box['fields'] );
							$all_fields = array_merge($all_fields, $current_fields );

							foreach ($group_meta_box['fields'] as $field_id => $field) {
								$all_fields_by_type[ $field['type'] ][] = $field_id;
							}

						}
					}
				}
			}

			foreach ($_POST as $current_meta_key => $current_meta_value) {
				if( in_array($current_meta_key, $all_fields)) {
					update_post_meta( $post_id, $current_meta_key, $current_meta_value );
				}
			}

			if( isset( $all_fields_by_type['checkbox'] ) ) {

				foreach ($all_fields_by_type['checkbox'] as $key => $checkbox) {
					if( ! in_array( $checkbox, array_keys( $_POST ) ) ) {
						update_post_meta( $post_id, $checkbox, 'no' );
					}
				}
			}


			// /**
			//  * Get meta options
			//  */
			// $post_meta = self::get_meta_option();

			// foreach ( $post_meta as $key => $data ) {

			// 	// Sanitize values.
			// 	$sanitize_filter = ( isset( $data['sanitize'] ) ) ? $data['sanitize'] : 'FILTER_DEFAULT';

			// 	switch ( $sanitize_filter ) {

			// 		case 'FILTER_SANITIZE_STRING':
			// 				$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
			// 			break;

			// 		case 'FILTER_SANITIZE_URL':
			// 				$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_URL );
			// 			break;

			// 		case 'FILTER_SANITIZE_NUMBER_INT':
			// 				$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );
			// 			break;

			// 		default:
			// 				$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );
			// 			break;
			// 	}

			// 	// Store values.
			// 	if ( $meta_value ) {
			// 		update_post_meta( $post_id, $key, $meta_value );
			// 	} else {
			// 		delete_post_meta( $post_id, $key );
			// 	}
			// }

		}

		/**
		 *  Setup Metabox
		 */
		function setup_meta_box() {

			if( empty( $this->meta_boxes ) ) {
				return;
			}

			$this->check_duplicates();

			foreach ($this->meta_boxes as $key => $meta_box) {
				add_meta_box(
					$meta_box['id'],                   // Id.
					$meta_box['title'],                // Title.
					array( $this, 'markup_meta_box' ), // Callback.
					$meta_box['screen'],               // Post_type.
					$meta_box['context'],              // Context.
					$meta_box['priority'],             // Priority.
					$meta_box 						   // Callback Args.
				);

			}
		}

		/**
		 * Metabox Markup
		 *
		 * @param  object $post Post object.
		 * @return void
		 */
		function markup_meta_box( $post, $meta_box ) {

			wp_nonce_field( basename( __FILE__ ), 'wp_meta_fields_nonce' );

			$fields = isset( $meta_box['args']['fields'] ) ? $meta_box['args']['fields'] : array();

			if( ! empty( $fields ) ) {
				?>
					<table class="widefat wp-meta-fields-table">
						<tbody>
							<?php foreach ($fields as $meta_key => $field) { ?>
							<?php 	$this->generate_markup( $post->ID, $meta_key, $field, $meta_box ); ?>
							<?php } ?>
						</tbody>
					</table>
				<?php
			}

			$groups = isset( $meta_box['args']['groups'] ) ? $meta_box['args']['groups'] : array();

			if( ! empty( $groups ) ) {
				foreach ($groups as $key => $group) {
					?>
					<div class="wp-meta-fields-title">
						<h3><?php echo $group['title']; ?></h3>
						<p class="description"><?php echo $group['description']; ?></p>
					</div>
					
					<?php if( ! empty( $group['fields'] ) ) { ?>
						<table class="widefat wp-meta-fields-table">
							<tbody>
								<?php foreach ($group['fields'] as $meta_key => $field) { ?>
								<?php 	$this->generate_markup( $post->ID, $meta_key, $field, $meta_box ); ?>
								<?php } ?>
							</tbody>
						</table>
					<?php } ?>
					<?php
				}
			}

		}

		function check_duplicates() {
			$all_fields = array();
			foreach ($this->meta_boxes as $key => $meta_box) {
				if( isset( $meta_box['fields'] ) ) {
					$all_fields[] = array_keys( $meta_box['fields'] );
				} else if( isset( $meta_box['groups'] ) ) {
					if( ! empty( $meta_box['groups'] ) ) {
						foreach ($meta_box['groups'] as $key => $group_meta_box) {
							$all_fields[] = array_keys( $group_meta_box['fields'] );
						}
					}
				}
			}

			foreach ($all_fields as $key => $meta_keys) {
				if( is_array( $meta_keys ) ) {
					if( ! empty( $meta_keys ) ) {
						foreach ($meta_keys as $current_key => $current_meta_key) {
							if( in_array( $current_meta_key, $this->unique_keys ) ) {
								$this->duplicate_keys[] = $current_meta_key;
							} else {
								$this->unique_keys[] = $current_meta_key;
							}
						}
					}
				}
			}

		}

		function add_meta_box( $args = array() ) {
			$this->meta_boxes[] = $args;
		}

		function get_meta( $meta_key = '', $post_id = '' ) {

			if( empty( $post_id ) ) {
				$post_id = get_the_ID();
			}

			return get_post_meta( $post_id, $meta_key, true );
		}

		function meta( $meta_key = '', $post_id = '' ) {
			echo $this->get_meta( $meta_key, $post_id);
		}

		function generate_markup( $post_id, $meta_key = '', $field = array(), $meta_box ) {

			$duplicate_class = in_array( $meta_key, $this->duplicate_keys ) ? 'wp-meta-fields-duplicate' : '';
			$readonly        = in_array( $meta_key, $this->duplicate_keys ) ? 'readonly' : '';
			$duplicate_message = '';
			if( in_array( $meta_key, $this->duplicate_keys ) ) { ?>
				<div class="notice notice-warning"><?php printf( __( '<p>Meta key <code>%1$s</code> is duplicate from meta box <b>%2$s</b>. Please use unique meta key.</p>', 'wp-meta-fields' ), $meta_key, $meta_box['title'] ); ?></div>
				<?php
			}

			$value = get_post_meta( $post_id, $meta_key, true );

			if( empty( $value ) ) {
				$value = isset( $field['default'] ) ? $field['default'] : '';
			}

			switch ( $field['type'] ) {
				case 'text':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="text" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" />
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'textarea':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<textarea name="<?php echo $meta_key; ?>" <?php echo $readonly; ?>><?php echo $value; ?></textarea>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'radio':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<?php foreach ($field['choices']  as $choice_value => $choice_title) { ?>
										<label><input type="radio" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" <?php checked( $value, $choice_value ); ?> value="<?php echo $choice_value; ?>" /><?php echo $choice_title; ?><br/></label>
									<?php } ?>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'password':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="password" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'color':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="color" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'date':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="date" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'email':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="email" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'month':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="month" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'number':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="number" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'time':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="time" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'url':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="url" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'week':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="week" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'datetime-local':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="datetime-local" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" value="<?php echo $value; ?>" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'checkbox':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<input type="checkbox" <?php echo $readonly; ?> name="<?php echo $meta_key; ?>" <?php checked( $value, 'yes' ); ?> value="yes" /><br/>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
				case 'select':
							?>
							<tr class="wp-meta-fields-row <?php echo $duplicate_class; ?>">
								<td class="wp-meta-fields-heading"><?php echo $field['title']; ?></td>
								<td class="wp-meta-fields-content">
									<?php echo $duplicate_message; ?>
									<?php if( ! empty( $field['description'] ) ) { ?>
										<div class="wp-meta-fields-hint">
											<i class="dashicons dashicons-editor-help" onClick="jQuery(this).siblings('.wp-meta-fields-hint-message').slideToggle(000);"></i>
											<p class="wp-meta-fields-hint-message" style="display: none;"><?php echo $field['hint']; ?></p>
										</div>
									<?php } ?>
									<select name="<?php echo $meta_key; ?>" <?php echo $readonly; ?>>
									<?php foreach ($field['choices']  as $choice_value => $choice_title) { ?>
										<option value="<?php echo $choice_value; ?>" <?php selected( $value, $choice_value ); ?>><?php echo $choice_title; ?></option>
									<?php } ?>
									</select>
									<p class="description"><?php echo $field['description']; ?></p>
								</td>
							</tr>
							<?php
					break;
			}
		}
	}
	
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	WP_Meta_Fields::get_instance();

}// End if().

function mf_add_meta_box( $args = array() ) {
	WP_Meta_Fields::get_instance()->add_meta_box( $args );
}

function mf_get_meta( $meta_key = '', $post_id = '' ) {
	return WP_Meta_Fields::get_instance()->get_meta( $meta_key, $post_id );
}

function mf_meta( $meta_key = '', $post_id = '' ) {
	return WP_Meta_Fields::get_instance()->meta( $meta_key, $post_id );
}