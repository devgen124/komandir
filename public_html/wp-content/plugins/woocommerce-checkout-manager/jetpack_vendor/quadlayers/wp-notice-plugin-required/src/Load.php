<?php

namespace QuadLayers\WP_Notice_Plugin_Required;

/**
 * Class Load
 *
 * @package QuadLayers\WP_Notice_Plugin_Required
 */

class Load {

	/**
	 * Required Plugins.
	 *
	 * @var array
	 */
	protected $plugins;
	/**
	 * Current Plugin name.
	 *
	 * @var string
	 */
	protected $current_plugin_name = '';

	public function __construct( string $current_plugin_name, array $plugins = array() ) {
		$this->current_plugin_name = $current_plugin_name;
		$this->plugins             = $plugins;
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	function admin_notices() {

		$screen = get_current_screen();

		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		foreach ( $this->plugins as $plugin ) {

			if ( ! isset( $plugin['slug'], $plugin['name'] ) ) {
				continue;
			}

			$plugin = Plugin::get_instance( $plugin['slug'], $plugin['name'] );

			$notice = $this->add_notice( $plugin );

			/**
			 * If notice is added then return.
			 * This will prevent multiple notices for same plugin.
			 */
			if ( $notice ) {
				return;
			}
		}
	}

	private function add_notice( Plugin $plugin ) {

		if ( $plugin->is_plugin_activated() ) {
			return false;
		}

		if ( $plugin->is_plugin_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return false;
			}
			?>
			<div class="error">
				<p>
					<a href="<?php echo esc_url( $plugin->get_plugin_activate_link() ); ?>" class='button button - secondary'><?php printf( esc_html__( 'Activate % s', 'wp - notice - plugin - required' ), esc_html( $plugin->get_plugin_name() ) ); ?></a>
					<?php printf( esc_html__( '%1$s not working because you need to activate the %2$s plugin . ', 'wp - notice - plugin - required' ), esc_html( $this->current_plugin_name ), esc_html( $plugin->get_plugin_name() ) ); ?>
				</p>
			</div>
			<?php
			return true;
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}
		?>
		<div class="error">
			<p>
				<a href="<?php echo esc_url( $plugin->get_plugin_install_link() ); ?>" class='button button - secondary'><?php printf( esc_html__( 'Install % s', 'wp - notice - plugin - required' ), esc_html( $plugin->get_plugin_name() ) ); ?></a>
				<?php printf( esc_html__( '%1$s not working because you need to install the %2$s plugin . ', 'wp - notice - plugin - required' ), esc_html( $this->current_plugin_name ), esc_html( $plugin->get_plugin_name() ) ); ?>
			</p>
		</div>
		<?php
		return true;
	}

}