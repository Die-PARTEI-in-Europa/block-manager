<?php
/**
 * Uninstall handler: remove plugin data.
 *
 * @package WP_Block_Manager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'parteieuropa_block_manager_disabled_blocks' );
