<?php
/**
 * Plugin Name:       parteieuropa.eu - Block Manager
 * Plugin URI:        https://github.com/Die-PARTEI-in-Europa/parteieuropa-block-manager
 * Description:       A simple admin screen to enable or disable individual Gutenberg blocks in the editor via allowed_block_types_all. Works with any registered block.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            parteieuropa.eu
 * Author URI:        https://parteieuropa.eu
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       parteieuropa-block-manager
 * Domain Path:       /languages
 *
 * @package WP_Block_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PARTEIEUROPA_BLOCK_MANAGER_VERSION' ) ) {
	define( 'PARTEIEUROPA_BLOCK_MANAGER_VERSION', '1.0.0' );
}

if ( ! class_exists( 'Parteieuropa_Block_Manager' ) ) :

	/**
	 * Core plugin class: registers the admin screen and filters allowed blocks.
	 */
	class Parteieuropa_Block_Manager {

		/**
		 * Option key holding the list of disabled block names.
		 *
		 * @var string
		 */
		const OPTION = 'parteieuropa_block_manager_disabled_blocks';

		/**
		 * Admin page slug.
		 *
		 * @var string
		 */
		const PAGE = 'parteieuropa-block-manager';

		/**
		 * Wire up hooks.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_page' ) );
			add_action( 'admin_post_parteieuropa_block_manager_save', array( $this, 'save' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_filter( 'allowed_block_types_all', array( $this, 'filter_allowed_blocks' ), 10, 2 );
		}

		/**
		 * Register the settings submenu page.
		 */
		public function add_page() {
			add_submenu_page(
				'options-general.php',
				__( 'Block Manager', 'parteieuropa-block-manager' ),
				__( 'Block Manager', 'parteieuropa-block-manager' ),
				'manage_options',
				self::PAGE,
				array( $this, 'render_page' )
			);
		}

		/**
		 * Enqueue admin CSS/JS only on this plugin's screen.
		 *
		 * @param string $hook Current admin page hook suffix.
		 */
		public function enqueue_assets( $hook ) {
			if ( 'settings_page_' . self::PAGE !== $hook ) {
				return;
			}
			wp_enqueue_style(
				'parteieuropa-block-manager-admin',
				plugins_url( 'assets/admin.css', __FILE__ ),
				array(),
				PARTEIEUROPA_BLOCK_MANAGER_VERSION
			);
			wp_enqueue_script(
				'parteieuropa-block-manager-admin',
				plugins_url( 'assets/admin.js', __FILE__ ),
				array(),
				PARTEIEUROPA_BLOCK_MANAGER_VERSION,
				true
			);
		}

		/**
		 * Restrict the blocks available in the editor to the enabled set.
		 *
		 * @param bool|string[]           $allowed_blocks Current allow list (or true for all).
		 * @param WP_Block_Editor_Context $context        Editor context (unused).
		 * @return bool|string[] Filtered allow list.
		 */
		public function filter_allowed_blocks( $allowed_blocks, $context ) {
			$disabled = (array) get_option( self::OPTION, array() );
			if ( empty( $disabled ) ) {
				return $allowed_blocks;
			}

			$all = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );
			return array_values( array_diff( $all, $disabled ) );
		}

		/**
		 * Persist the disabled-blocks list from the submitted form.
		 */
		public function save() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You are not allowed to do this.', 'parteieuropa-block-manager' ) );
			}
			check_admin_referer( 'parteieuropa_block_manager_save' );

			$all = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );

			$enabled = array();
			if ( isset( $_POST['blocks'] ) && is_array( $_POST['blocks'] ) ) {
				$enabled = array_map( 'sanitize_text_field', wp_unslash( $_POST['blocks'] ) );
			}

			$disabled = array_values( array_diff( $all, $enabled ) );
			update_option( self::OPTION, $disabled );

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'  => self::PAGE,
						'saved' => '1',
					),
					admin_url( 'options-general.php' )
				)
			);
			exit;
		}

		/**
		 * Build a slug => localized title map of block categories.
		 *
		 * Uses WordPress' own default categories so labels are translated by
		 * core; unknown/custom categories fall back to a humanized slug. No
		 * category is hard-coded or added by this plugin.
		 *
		 * @return array<string,string>
		 */
		private function category_labels() {
			$labels = array();
			if ( function_exists( 'get_default_block_categories' ) ) {
				foreach ( get_default_block_categories() as $cat ) {
					if ( isset( $cat['slug'], $cat['title'] ) ) {
						$labels[ $cat['slug'] ] = $cat['title'];
					}
				}
			}
			$labels['uncategorized'] = __( 'Uncategorized', 'parteieuropa-block-manager' );
			return $labels;
		}

		/**
		 * Render the admin screen.
		 */
		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$all_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
			$disabled   = (array) get_option( self::OPTION, array() );
			$labels     = $this->category_labels();

			// Group blocks by their own category.
			$grouped = array();
			foreach ( $all_blocks as $name => $block ) {
				$cat                      = ! empty( $block->category ) ? $block->category : 'uncategorized';
				$grouped[ $cat ][ $name ] = $block;
			}
			ksort( $grouped );

			$saved = isset( $_GET['saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="wrap fbm">
				<h1 class="fbm__title">
					<span class="fbm__icon" aria-hidden="true">🧩</span>
					<?php echo esc_html__( 'Block Manager', 'parteieuropa-block-manager' ); ?>
				</h1>
				<p class="fbm__intro"><?php echo esc_html__( 'Choose which blocks are available in the block editor.', 'parteieuropa-block-manager' ); ?></p>

				<?php if ( $saved ) : ?>
					<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'parteieuropa-block-manager' ); ?></p></div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="parteieuropa_block_manager_save">
					<?php wp_nonce_field( 'parteieuropa_block_manager_save' ); ?>

					<div class="fbm__toolbar">
						<button type="button" class="button" data-fbm-toggle="on"><?php echo esc_html__( 'Enable all', 'parteieuropa-block-manager' ); ?></button>
						<button type="button" class="button" data-fbm-toggle="off"><?php echo esc_html__( 'Disable all', 'parteieuropa-block-manager' ); ?></button>
						<?php submit_button( __( 'Save', 'parteieuropa-block-manager' ), 'primary', 'submit', false ); ?>
					</div>

					<?php foreach ( $grouped as $cat => $blocks ) : ?>
						<?php
						$label = isset( $labels[ $cat ] ) ? $labels[ $cat ] : ucwords( str_replace( array( '-', '_' ), ' ', $cat ) );
						$count = count( $blocks );
						?>
						<div class="fbm__group">
							<div class="fbm__group-head">
								<strong><?php echo esc_html( $label ); ?></strong>
								<span class="fbm__count">
									<?php
									/* translators: %s: number of blocks in the category. */
									echo esc_html( sprintf( _n( '%s block', '%s blocks', $count, 'parteieuropa-block-manager' ), number_format_i18n( $count ) ) );
									?>
								</span>
							</div>
							<div class="fbm__grid">
								<?php foreach ( $blocks as $name => $block ) : ?>
									<?php
									$is_enabled = ! in_array( $name, $disabled, true );
									$title      = ! empty( $block->title ) ? $block->title : $name;
									?>
									<label class="fbm__item">
										<input type="checkbox" name="blocks[]" value="<?php echo esc_attr( $name ); ?>" class="fbm__cb" <?php checked( $is_enabled ); ?>>
										<span>
											<span class="fbm__name"><?php echo esc_html( $title ); ?></span>
											<span class="fbm__slug"><?php echo esc_html( $name ); ?></span>
										</span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>

					<div class="fbm__footer">
						<?php submit_button( __( 'Save', 'parteieuropa-block-manager' ), 'primary', 'submit', false ); ?>
					</div>
				</form>
			</div>
			<?php
		}
	}

	new Parteieuropa_Block_Manager();

endif;
