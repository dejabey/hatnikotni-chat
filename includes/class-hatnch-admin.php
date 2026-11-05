<?php
/**
 * WordPress admin interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Admin {

	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_post_hatnch_save_contact', array( __CLASS__, 'save_contact' ) );
		add_action( 'admin_post_hatnch_toggle_contact', array( __CLASS__, 'toggle_contact' ) );
		add_action( 'admin_post_hatnch_set_default_contact', array( __CLASS__, 'set_default_contact' ) );
		add_action( 'admin_post_hatnch_delete_contact', array( __CLASS__, 'delete_contact' ) );
		add_action( 'admin_post_hatnch_save_settings', array( __CLASS__, 'save_settings' ) );
	}

	public static function enqueue_assets( string $hook_suffix ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page selector; no state-changing action.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'toplevel_page_hatnch' !== $hook_suffix && ! in_array( $page, array( 'hatnch', 'hatnch-contacts', 'hatnch-analytics' ), true ) ) {
			return;
		}

		wp_enqueue_style(
			'hatnch-admin',
			HATNCH_PLUGIN_URL . 'assets/css/hatnikotni-chat-admin.css',
			array(),
			HATNCH_VERSION
		);

		wp_enqueue_script( 'hatnch-admin-tabs', HATNCH_PLUGIN_URL . 'assets/js/hatnikotni-chat-admin-tabs.js', array(), HATNCH_VERSION, true );
	}

	public static function register_menu(): void {
		add_menu_page(
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			'manage_options',
			'hatnch',
			array( __CLASS__, 'render_general' ),
			'dashicons-format-chat',
			58
		);

		add_submenu_page( 'hatnch', __( 'General', 'hatnikotni-chat' ), __( 'General', 'hatnikotni-chat' ), 'manage_options', 'hatnch', array( __CLASS__, 'render_general' ) );
		add_submenu_page( 'hatnch', __( 'Contacts', 'hatnikotni-chat' ), __( 'Contacts', 'hatnikotni-chat' ), 'manage_options', 'hatnch-contacts', array( __CLASS__, 'render_contacts' ) );
	}

	/** Render persistent top-level workspace navigation. */
	public static function render_workspace_nav( string $active ): void {
		$items = array(
			'general' => array( 'label' => __( 'General', 'hatnikotni-chat' ), 'url' => admin_url( 'admin.php?page=hatnch' ) ),
			'contacts' => array( 'label' => __( 'Contacts', 'hatnikotni-chat' ), 'url' => admin_url( 'admin.php?page=hatnch-contacts' ) ),
			'analytics' => array( 'label' => __( 'Analytics', 'hatnikotni-chat' ), 'url' => admin_url( 'admin.php?page=hatnch-analytics' ) ),
		);
		echo '<nav class="hatnch-workspace-nav" aria-label="' . esc_attr__( 'Hatnikotni Chat workspace', 'hatnikotni-chat' ) . '">';
		foreach ( $items as $key => $item ) {
			$class = 'hatnch-workspace-nav__link' . ( $active === $key ? ' is-active' : '' );
			$aria  = $active === $key ? ' aria-current="page"' : '';
			echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $item['url'] ) . '"' . $aria . '>' . esc_html( $item['label'] ) . '</a>';
		}
		echo '</nav>';
	}

	public static function render_general(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hatnikotni-chat' ) );
		}

		$contacts = HATNCH_Contacts::get_all();
		$settings = HATNCH_Settings::all();
		?>
		<div class="wrap hatnch-admin">
			<header class="hatnch-page-header">
				<div>
					<p class="hatnch-eyebrow"><?php echo esc_html__( 'WhatsApp contact layer', 'hatnikotni-chat' ); ?></p>
					<h1><?php echo esc_html__( 'Hatnikotni Chat', 'hatnikotni-chat' ); ?></h1>
					<p class="hatnch-admin-intro"><?php echo esc_html__( 'Configure how visitors reach your WhatsApp contacts and how the button appears across the site.', 'hatnikotni-chat' ); ?></p>
				</div>
				<div class="hatnch-page-header__status">
					<span class="hatnch-status-badge <?php echo ! empty( $settings['enabled'] ) ? 'is-active' : 'is-inactive'; ?>">
						<?php echo ! empty( $settings['enabled'] ) ? esc_html__( 'Enabled', 'hatnikotni-chat' ) : esc_html__( 'Disabled', 'hatnikotni-chat' ); ?>
					</span>
				</div>
			</header>
			<?php self::render_workspace_nav( 'general' ); ?>

			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only status notice flag.
			if ( isset( $_GET['updated'] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'hatnikotni-chat' ) . '</p></div>';
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only error notice flag.
			if ( isset( $_GET['error'] ) ) {
				$error_messages = array(
					'missing_name'         => __( 'Contact name is required.', 'hatnikotni-chat' ),
					'invalid_phone'        => __( 'Enter a valid WhatsApp number using international digits only, without +, spaces or hyphens.', 'hatnikotni-chat' ),
					'name_too_long'        => __( 'Contact name must be 100 characters or fewer.', 'hatnikotni-chat' ),
					'role_too_long'        => __( 'Contact role must be 100 characters or fewer.', 'hatnikotni-chat' ),
					'description_too_long' => __( 'Contact description must be 255 characters or fewer.', 'hatnikotni-chat' ),
					'db_update_failed'     => __( 'The contact could not be updated.', 'hatnikotni-chat' ),
					'db_insert_failed'     => __( 'The contact could not be created.', 'hatnikotni-chat' ),
					'delete_failed'        => __( 'The contact could not be deleted. Analytics data was left intact.', 'hatnikotni-chat' ),
					'contact_not_found'    => __( 'The contact no longer exists.', 'hatnikotni-chat' ),
				);
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only error notice flag, sanitized and allow-listed below.
				$error_key = sanitize_key( wp_unslash( $_GET['error'] ) );
				if ( isset( $error_messages[ $error_key ] ) ) {
					echo '<div class="notice notice-error"><p>' . esc_html( $error_messages[ $error_key ] ) . '</p></div>';
				}
			}
			?>

			<nav class="hatnch-settings-tabs" aria-label="<?php echo esc_attr__( 'Settings sections', 'hatnikotni-chat' ); ?>" role="tablist">
				<button type="button" class="hatnch-settings-tab is-active" id="hatnch-tab-setup" role="tab" aria-selected="true" aria-controls="hatnch-pane-setup" data-hatnch-tab="setup"><?php echo esc_html__( 'Setup', 'hatnikotni-chat' ); ?></button>
				<button type="button" class="hatnch-settings-tab" id="hatnch-tab-routing" role="tab" aria-selected="false" aria-controls="hatnch-pane-routing" data-hatnch-tab="routing" tabindex="-1"><?php echo esc_html__( 'Routing', 'hatnikotni-chat' ); ?></button>
				<button type="button" class="hatnch-settings-tab" id="hatnch-tab-integrations" role="tab" aria-selected="false" aria-controls="hatnch-pane-integrations" data-hatnch-tab="integrations" tabindex="-1"><?php echo esc_html__( 'Extensions', 'hatnikotni-chat' ); ?></button>
			</nav>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hatnch-settings-form">
				<input type="hidden" name="action" value="hatnch_save_settings">
				<input type="hidden" name="active_tab" value="setup" data-hatnch-active-tab>
				<?php wp_nonce_field( 'hatnch_save_settings', 'hatnch_nonce' ); ?>

				<div class="hatnch-tab-panel is-active" id="hatnch-pane-setup" role="tabpanel" aria-labelledby="hatnch-tab-setup" data-hatnch-pane="setup">
				<section class="hatnch-panel">
					<div class="hatnch-panel__header">
						<div>
							<h2><?php echo esc_html__( 'Core settings', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Control whether the site-wide WhatsApp button is active and which contact receives visitors by default.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php echo esc_html__( 'Enable', 'hatnikotni-chat' ); ?></th>
							<td><label class="hatnch-toggle-label"><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>> <span><?php echo esc_html__( 'Enable Hatnikotni Chat frontend button', 'hatnikotni-chat' ); ?></span></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="hatnch-default-contact"><?php echo esc_html__( 'Default Contact', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<select id="hatnch-default-contact" name="default_contact">
									<option value="0"><?php echo esc_html__( '— Select —', 'hatnikotni-chat' ); ?></option>
									<?php foreach ( $contacts as $contact ) : ?>
										<option value="<?php echo esc_attr( $contact['id'] ); ?>" <?php selected( (int) ( $settings['default_contact'] ?? 0 ), (int) $contact['id'] ); ?>>
											<?php echo esc_html( $contact['name'] . ( 1 !== (int) $contact['status'] ? ' — Inactive' : '' ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php echo esc_html__( 'Choose an active contact. The contact’s WhatsApp number is managed under Contacts.', 'hatnikotni-chat' ); ?></p>
							</td>
						</tr>
					</table>
				</section>

				<section class="hatnch-panel">
					<div class="hatnch-panel__header">
						<div>
							<h2><?php echo esc_html__( 'Button & message', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Set the visitor-facing label, optional pre-filled message and placement of the floating button.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="hatnch-default-message"><?php echo esc_html__( 'Default Message', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<textarea id="hatnch-default-message" name="default_message" rows="4" class="large-text" maxlength="1000" placeholder="<?php echo esc_attr__( 'Example: Hello, I would like to ask about your services.', 'hatnikotni-chat' ); ?>"><?php echo esc_textarea( $settings['default_message'] ?? '' ); ?></textarea>
								<p class="description"><?php echo esc_html__( 'Optional pre-filled message. Keep it short and relevant to a first contact.', 'hatnikotni-chat' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="hatnch-button-label"><?php echo esc_html__( 'Button Label', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<input id="hatnch-button-label" name="button_label" type="text" class="regular-text" maxlength="80" placeholder="<?php echo esc_attr__( 'Contact Us', 'hatnikotni-chat' ); ?>" value="<?php echo esc_attr( $settings['button_label'] ?? 'Contact Us' ); ?>">
								<p class="description"><?php echo esc_html__( 'Text visitors see on the WhatsApp button.', 'hatnikotni-chat' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="hatnch-button-position"><?php echo esc_html__( 'Button Position', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<select id="hatnch-button-position" name="button_position">
									<option value="right" <?php selected( $settings['button_position'] ?? 'right', 'right' ); ?>><?php echo esc_html__( 'Bottom right', 'hatnikotni-chat' ); ?></option>
									<option value="left" <?php selected( $settings['button_position'] ?? 'right', 'left' ); ?>><?php echo esc_html__( 'Bottom left', 'hatnikotni-chat' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html__( 'Visibility', 'hatnikotni-chat' ); ?></th>
							<td>
								<div class="hatnch-option-list">
									<label class="hatnch-toggle-label"><input type="checkbox" name="show_desktop" value="1" <?php checked( ! empty( $settings['show_desktop'] ) ); ?>> <span><?php echo esc_html__( 'Desktop', 'hatnikotni-chat' ); ?></span></label>
									<label class="hatnch-toggle-label"><input type="checkbox" name="show_mobile" value="1" <?php checked( ! empty( $settings['show_mobile'] ) ); ?>> <span><?php echo esc_html__( 'Mobile', 'hatnikotni-chat' ); ?></span></label>
								</div>
							</td>
						</tr>
					</table>
				</section>

				</div><!-- /.hatnch-tab-panel setup -->
				<div class="hatnch-tab-panel" id="hatnch-pane-routing" role="tabpanel" aria-labelledby="hatnch-tab-routing" data-hatnch-pane="routing" hidden>
				<section class="hatnch-panel">
					<div class="hatnch-panel__header">
						<div>
							<h2><?php echo esc_html__( 'Routing', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Choose how active contacts are selected. Optional modules can extend purpose and role behavior for this website.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="hatnch-routing-method"><?php echo esc_html__( 'Routing Method', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<select id="hatnch-routing-method" name="routing_method">
									<option value="direct" <?php selected( $settings['routing_method'] ?? 'direct', 'direct' ); ?>><?php echo esc_html__( 'Direct', 'hatnikotni-chat' ); ?></option>
									<option value="random" <?php selected( $settings['routing_method'] ?? 'direct', 'random' ); ?>><?php echo esc_html__( 'Random', 'hatnikotni-chat' ); ?></option>
									<option value="round_robin" <?php selected( $settings['routing_method'] ?? 'direct', 'round_robin' ); ?>><?php echo esc_html__( 'Round robin', 'hatnikotni-chat' ); ?></option>
								</select>
							</td>
						</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Purpose-based routing', 'hatnikotni-chat' ); ?></th>
						<td><label><input type="checkbox" name="purpose_routing_enabled" value="1" <?php checked( ! empty( $settings['purpose_routing_enabled'] ) ); ?>> <?php echo esc_html__( 'Enable the purpose chooser for the floating button', 'hatnikotni-chat' ); ?></label><p class="description"><?php echo esc_html__( 'When disabled, the existing global routing and button behavior remain in effect.', 'hatnikotni-chat' ); ?></p></td>
					</tr>
					<?php $purpose_map = is_array( $settings['purpose_roles'] ?? null ) ? $settings['purpose_roles'] : array(); $role_choices = array( 'general_enquiries' => __( 'General Enquiries', 'hatnikotni-chat' ), 'business_support' => __( 'Business Support', 'hatnikotni-chat' ), 'partnerships' => __( 'Sales & Partnerships', 'hatnikotni-chat' ) ); ?>
					<?php foreach ( array( 'general_enquiries' => __( 'General Enquiries', 'hatnikotni-chat' ), 'listing_assistance' => __( 'Customer Support', 'hatnikotni-chat' ), 'business_partnerships' => __( 'Sales & Partnerships', 'hatnikotni-chat' ), 'technical_question' => __( 'Technical Support', 'hatnikotni-chat' ) ) as $purpose_key => $purpose_name ) : ?>
						<tr><th scope="row"><label for="hatnch-purpose-<?php echo esc_attr( $purpose_key ); ?>"><?php echo esc_html( $purpose_name ); ?></label></th><td><select id="hatnch-purpose-<?php echo esc_attr( $purpose_key ); ?>" name="purpose_roles[<?php echo esc_attr( $purpose_key ); ?>]"><?php foreach ( $role_choices as $role_key => $role_name ) : ?><option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $purpose_map[ $purpose_key ] ?? '', $role_key ); ?>><?php echo esc_html( $role_name ); ?></option><?php endforeach; ?></select></td></tr>
					<?php endforeach; ?>
					<tr><th scope="row"><label for="hatnch-support-email"><?php echo esc_html__( 'Support Email', 'hatnikotni-chat' ); ?></label></th><td><input class="regular-text" id="hatnch-support-email" name="support_email" type="email" maxlength="254" value="<?php echo esc_attr( $settings['support_email'] ?? '' ); ?>"><p class="description"><?php echo esc_html__( 'Optional alternative support email for complex matters or payment investigations.', 'hatnikotni-chat' ); ?></p></td></tr>
					<tr><th scope="row"><label for="hatnch-support-url"><?php echo esc_html__( 'Support Page URL', 'hatnikotni-chat' ); ?></label></th><td><input class="regular-text code" id="hatnch-support-url" name="support_url" type="url" maxlength="2048" value="<?php echo esc_attr( $settings['support_url'] ?? '' ); ?>" placeholder="https://example.com/contact/"><p class="description"><?php echo esc_html__( 'Optional link to a contact form or dedicated support page. This is offered for complex matters or payment investigations.', 'hatnikotni-chat' ); ?></p></td></tr>
					</table>
				</section>

				</div><!-- /.hatnch-tab-panel routing -->
				<div class="hatnch-tab-panel" id="hatnch-pane-integrations" role="tabpanel" aria-labelledby="hatnch-tab-integrations" data-hatnch-pane="integrations" hidden>
					<section class="hatnch-panel">
						<div class="hatnch-panel__header"><div><h2><?php echo esc_html__( 'Extension modules', 'hatnikotni-chat' ); ?></h2><p><?php echo esc_html__( 'Optional modules can add site-specific features without changing the core plugin.', 'hatnikotni-chat' ); ?></p></div></div>
						<?php $extension_panels = apply_filters( 'hatnch_admin_extension_panels', array() ); ?>
						<?php if ( is_array( $extension_panels ) && ! empty( $extension_panels ) ) : ?>
							<?php foreach ( $extension_panels as $extension_panel ) : if ( ! is_array( $extension_panel ) || empty( $extension_panel['callback'] ) || ! is_callable( $extension_panel['callback'] ) ) { continue; } ?>
								<section class="hatnch-extension-panel"><h3><?php echo esc_html( $extension_panel['title'] ?? __( 'Extension', 'hatnikotni-chat' ) ); ?></h3><?php call_user_func( $extension_panel['callback'] ); ?></section>
							<?php endforeach; ?>
						<?php else : ?><p class="description"><?php echo esc_html__( 'No extension modules are currently providing settings.', 'hatnikotni-chat' ); ?></p><?php endif; ?>
					</section>
				</div><!-- /.hatnch-tab-panel integrations -->
				<div class="hatnch-form-actions">
					<?php submit_button( __( 'Save Settings', 'hatnikotni-chat' ), 'primary', '', false ); ?>
				</div>
			</form>
		</div>
		<?php
	}

	public static function save_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ) );
		}

		check_admin_referer( 'hatnch_save_settings', 'hatnch_nonce' );

		$contact_id = isset( $_POST['default_contact'] ) ? absint( $_POST['default_contact'] ) : 0;
		$contact    = $contact_id > 0 ? HATNCH_Contacts::get( $contact_id ) : null;
		$method     = isset( $_POST['routing_method'] ) ? sanitize_key( wp_unslash( $_POST['routing_method'] ) ) : 'direct';

		if ( ! in_array( $method, array( 'direct', 'random', 'round_robin' ), true ) ) {
			$method = 'direct';
		}

		$settings = array(
			'enabled'         => isset( $_POST['enabled'] ),
			'default_contact' => $contact && 1 === (int) $contact['status'] ? $contact_id : 0,
			'default_message' => isset( $_POST['default_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['default_message'] ) ) : '',
			'button_label'    => isset( $_POST['button_label'] ) ? sanitize_text_field( wp_unslash( $_POST['button_label'] ) ) : 'Contact Us',
			'button_position' => isset( $_POST['button_position'] ) && 'left' === sanitize_key( wp_unslash( $_POST['button_position'] ) ) ? 'left' : 'right',
			'show_desktop'    => isset( $_POST['show_desktop'] ),
			'show_mobile'     => isset( $_POST['show_mobile'] ),
			'routing_method'  => $method,
			'purpose_routing_enabled' => isset( $_POST['purpose_routing_enabled'] ),
			'purpose_roles' => array(),
			'support_email' => isset( $_POST['support_email'] ) ? sanitize_email( wp_unslash( $_POST['support_email'] ) ) : '',
			'support_url' => isset( $_POST['support_url'] ) ? esc_url_raw( wp_unslash( $_POST['support_url'] ), array( 'http', 'https' ) ) : '',
		);

		$allowed_roles = array( 'general_enquiries', 'business_support', 'partnerships' );
		$posted_routes = isset( $_POST['purpose_roles'] ) && is_array( $_POST['purpose_roles'] ) ? wp_unslash( $_POST['purpose_roles'] ) : array();
		foreach ( array( 'general_enquiries', 'listing_assistance', 'business_partnerships', 'technical_question' ) as $purpose_id ) {
			$selected_role = isset( $posted_routes[ $purpose_id ] ) && is_scalar( $posted_routes[ $purpose_id ] ) ? sanitize_key( $posted_routes[ $purpose_id ] ) : '';
			$settings['purpose_roles'][ $purpose_id ] = in_array( $selected_role, $allowed_roles, true ) ? $selected_role : 'general_enquiries';
		}
		update_option( 'hatnch_settings', $settings, false );

		$active_tab = isset( $_POST['active_tab'] ) ? sanitize_key( wp_unslash( $_POST['active_tab'] ) ) : 'setup';
		if ( ! in_array( $active_tab, array( 'setup', 'routing', 'integrations' ), true ) ) {
			$active_tab = 'setup';
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'hatnch', 'updated' => '1', 'tab' => $active_tab ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function render_contacts(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hatnikotni-chat' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only edit-form selector; mutations require a separate nonce-protected POST.
		$edit_id  = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		$editing  = $edit_id ? HATNCH_Contacts::get( $edit_id ) : null;
		$contacts = HATNCH_Contacts::get_all();
		$default_contact_id = absint( HATNCH_Settings::get( 'default_contact', 0 ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only form selector; mutations require a separate nonce-protected POST.
		$is_new = isset( $_GET['action'] ) && 'new' === sanitize_key( wp_unslash( $_GET['action'] ) );
		?>
		<div class="wrap hatnch-admin">
			<header class="hatnch-page-header">
				<div>
					<p class="hatnch-eyebrow"><?php echo esc_html__( 'Contact management', 'hatnikotni-chat' ); ?></p>
					<h1><?php echo esc_html__( 'Contacts', 'hatnikotni-chat' ); ?></h1>
					<p class="hatnch-admin-intro"><?php echo esc_html__( 'Manage the people or teams who receive WhatsApp enquiries. Phone numbers use international digits-only format.', 'hatnikotni-chat' ); ?></p>
				</div>
				<?php if ( ! $editing && ! $is_new ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=hatnch-contacts&action=new' ) ); ?>" class="hatnch-button hatnch-button--secondary"><?php echo esc_html__( 'Add Contact', 'hatnikotni-chat' ); ?></a>
				<?php endif; ?>
			</header>
			<?php self::render_workspace_nav( 'contacts' ); ?>

			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only status notice flag. ?>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Contact saved.', 'hatnikotni-chat' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['default_changed'] ) ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Default Contact updated. Direct routing will use this active contact. Analytics history was not changed.', 'hatnikotni-chat' ); ?></p></div><?php endif; ?>

			<?php if ( $editing || $is_new ) : ?>
				<?php self::render_contact_form( $editing ); ?>
			<?php endif; ?>

			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only result flag. ?>
			<?php if ( isset( $_GET['deleted'] ) ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Contact deleted. Historical analytics events were retained without contact identification.', 'hatnikotni-chat' ); ?></p></div><?php endif; ?>
			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only error status, allow-listed below. ?>
			<?php $contact_error = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : ''; ?>
			<?php if ( 'delete_failed' === $contact_error ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'The contact could not be deleted. Analytics data was left intact.', 'hatnikotni-chat' ); ?></p></div><?php elseif ( 'contact_not_found' === $contact_error ) : ?><div class="notice notice-warning"><p><?php echo esc_html__( 'The contact no longer exists.', 'hatnikotni-chat' ); ?></p></div><?php elseif ( 'default_contact_cannot_deactivate' === $contact_error ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'The Default Contact cannot be deactivated. Select another active Default Contact first.', 'hatnikotni-chat' ); ?></p></div><?php elseif ( 'contact_status_failed' === $contact_error ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'The contact status could not be updated.', 'hatnikotni-chat' ); ?></p></div><?php elseif ( 'default_contact_required' === $contact_error ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'Choose another active Default Contact before deleting or deactivating this contact.', 'hatnikotni-chat' ); ?></p></div><?php elseif ( 'default_contact_failed' === $contact_error ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'The Default Contact could not be changed.', 'hatnikotni-chat' ); ?></p></div><?php endif; ?>

			<section class="hatnch-panel hatnch-panel--table">
				<div class="hatnch-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Contact list', 'hatnikotni-chat' ); ?></h2>
						<p><?php echo esc_html__( 'Inactive contacts remain available for historical analytics and can be activated again later.', 'hatnikotni-chat' ); ?></p>
					</div>
					<span class="hatnch-count-badge"><?php echo esc_html( number_format_i18n( count( $contacts ) ) ); ?></span>
				</div>

				<div class="hatnch-table-wrap" role="region" aria-label="<?php echo esc_attr__( 'Contact list; scroll horizontally to view all columns', 'hatnikotni-chat' ); ?>" tabindex="0">
					<table class="widefat fixed striped hatnch-contacts-table">
						<thead>
							<tr>
								<th scope="col"><?php echo esc_html__( 'ID', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Name', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Phone', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Role', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Status', 'hatnikotni-chat' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $contacts ) ) : ?>
								<tr><td colspan="5"><?php echo esc_html__( 'No contacts yet.', 'hatnikotni-chat' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $contacts as $contact ) : ?>
									<tr>
										<td><span class="hatnch-contact-id"><?php echo esc_html( (string) $contact['id'] ); ?></span></td>
										<td><?php if ( $default_contact_id === absint( $contact['id'] ) ) : ?>
								<span class="hatnch-default-badge"><?php echo esc_html__( 'Default', 'hatnikotni-chat' ); ?></span>
							<?php endif; ?>
							<strong class="hatnch-contact-name" title="<?php echo esc_attr( $contact['name'] ); ?>"><?php echo esc_html( $contact['name'] ); ?></strong>
											<?php if ( ! empty( $contact['description'] ) ) : ?>
												<span class="hatnch-table-meta"><?php echo esc_html( $contact['description'] ); ?></span>
											<?php endif; ?>
											<div class="hatnch-row-actions">
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=hatnch-contacts&edit=' . (int) $contact['id'] ) ); ?>"><?php echo esc_html__( 'Edit', 'hatnikotni-chat' ); ?></a>
												<?php if ( $default_contact_id !== absint( $contact['id'] ) && 1 === (int) $contact['status'] ) : ?>
													<span aria-hidden="true">|</span>
													<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hatnch-row-action-form" onsubmit="return window.confirm(<?php echo esc_attr( wp_json_encode( sprintf( __( 'Set %1$s as the Default Contact? New Direct-routing interactions will go to %1$s. The previous Default Contact will remain active.', 'hatnikotni-chat' ), $contact['name'] ) ) ); ?>);">
														<input type="hidden" name="action" value="hatnch_set_default_contact">
														<input type="hidden" name="contact_id" value="<?php echo esc_attr( (string) $contact['id'] ); ?>">
														<?php wp_nonce_field( 'hatnch_set_default_contact_' . (int) $contact['id'], 'hatnch_default_nonce' ); ?>
														<button type="submit" class="hatnch-row-action-link"><?php echo esc_html__( 'Set as Default', 'hatnikotni-chat' ); ?></button>
													</form>
												<?php endif; ?>
								<?php if ( $default_contact_id !== absint( $contact['id'] ) ) : ?>
									<span aria-hidden="true">|</span>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hatnch-row-action-form">
										<input type="hidden" name="action" value="hatnch_toggle_contact">
										<input type="hidden" name="contact_id" value="<?php echo esc_attr( (string) $contact['id'] ); ?>">
										<?php wp_nonce_field( 'hatnch_toggle_contact_' . (int) $contact['id'], 'hatnch_toggle_nonce' ); ?>
										<button type="submit" class="hatnch-row-action-link"><?php echo 1 === (int) $contact['status'] ? esc_html__( 'Deactivate', 'hatnikotni-chat' ) : esc_html__( 'Activate', 'hatnikotni-chat' ); ?></button>
									</form>
									<span aria-hidden="true">|</span>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return window.confirm(<?php echo esc_attr( wp_json_encode( __( 'Permanently delete this contact? This cannot be undone. Historical analytics events will be retained but anonymized for this contact.', 'hatnikotni-chat' ) ) ); ?>);">
										<input type="hidden" name="action" value="hatnch_delete_contact">
										<input type="hidden" name="contact_id" value="<?php echo esc_attr( (string) $contact['id'] ); ?>">
										<?php wp_nonce_field( 'hatnch_delete_contact_' . (int) $contact['id'], 'hatnch_delete_nonce' ); ?>
										<button type="submit" class="hatnch-delete-contact"><?php echo esc_html__( 'Delete', 'hatnikotni-chat' ); ?></button>
									</form>
								<?php else : ?>
									<span class="hatnch-protected-note"><?php echo esc_html__( 'Protected while Default', 'hatnikotni-chat' ); ?></span>
								<?php endif; ?>
											</div>
										</td>
										<td><span class="hatnch-mono"><?php echo esc_html( $contact['phone'] ); ?></span></td>
										<td><?php echo esc_html( $contact['role'] ); ?></td>
										<td>
											<span class="hatnch-status-badge <?php echo 1 === (int) $contact['status'] ? 'is-active' : 'is-inactive'; ?>">
												<?php echo 1 === (int) $contact['status'] ? esc_html__( 'Active', 'hatnikotni-chat' ) : esc_html__( 'Inactive', 'hatnikotni-chat' ); ?>
											</span>
										</td>

									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
		</div>
		<?php
	}

	private static function render_contact_form( ?array $contact ): void {
		$contact = $contact ?? array(
			'id'          => 0,
			'name'        => '',
			'phone'       => '',
			'role'        => '',
			'description' => '',
			'status'      => 1,
			'weight'      => 1,
			'sort_order'  => 0,
		);
		?>
		<section class="hatnch-panel hatnch-contact-form">
			<div class="hatnch-panel__header">
				<div>
					<h2><?php echo $contact['id'] ? esc_html__( 'Edit Contact', 'hatnikotni-chat' ) : esc_html__( 'Add Contact', 'hatnikotni-chat' ); ?></h2>
					<p><?php echo esc_html__( 'Keep contact details accurate so routing and analytics remain reliable.', 'hatnikotni-chat' ); ?></p>
				</div>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hatnch-contact-form__body">
				<input type="hidden" name="action" value="hatnch_save_contact">
				<input type="hidden" name="id" value="<?php echo esc_attr( $contact['id'] ); ?>">
				<?php wp_nonce_field( 'hatnch_save_contact', 'hatnch_nonce' ); ?>
				<p class="hatnch-required-note"><span aria-hidden="true">*</span> <?php echo esc_html__( 'Required fields', 'hatnikotni-chat' ); ?></p>
				<div class="hatnch-contact-grid">
					<div class="hatnch-field">
						<label for="hatnch-name"><?php echo esc_html__( 'Name', 'hatnikotni-chat' ); ?> <span class="hatnch-required-mark" aria-hidden="true">*</span><span class="screen-reader-text"><?php echo esc_html__( '(required)', 'hatnikotni-chat' ); ?></span></label>
						<input id="hatnch-name" name="name" type="text" maxlength="100" required aria-required="true" autocomplete="name" value="<?php echo esc_attr( $contact['name'] ); ?>">
					</div>
					<div class="hatnch-field">
						<label for="hatnch-phone"><?php echo esc_html__( 'WhatsApp Number', 'hatnikotni-chat' ); ?> <span class="hatnch-required-mark" aria-hidden="true">*</span><span class="screen-reader-text"><?php echo esc_html__( '(required)', 'hatnikotni-chat' ); ?></span></label>
						<input id="hatnch-phone" name="phone" type="tel" maxlength="20" inputmode="numeric" pattern="[0-9]{8,20}" placeholder="<?php echo esc_attr__( '601234567890', 'hatnikotni-chat' ); ?>" required aria-required="true" autocomplete="tel-national" value="<?php echo esc_attr( $contact['phone'] ); ?>">
						<p class="description"><?php echo esc_html__( 'International digits only, without +, spaces or hyphens.', 'hatnikotni-chat' ); ?></p>
					</div>
					<div class="hatnch-field">
						<label for="hatnch-role"><?php echo esc_html__( 'Role', 'hatnikotni-chat' ); ?> <span class="hatnch-optional-mark"><?php echo esc_html__( 'Optional', 'hatnikotni-chat' ); ?></span></label>
						<select id="hatnch-role" name="role"><option value=""><?php echo esc_html__( 'Unassigned', 'hatnikotni-chat' ); ?></option><option value="general_enquiries" <?php selected( $contact['role'], 'general_enquiries' ); ?>><?php echo esc_html__( 'General Enquiries', 'hatnikotni-chat' ); ?></option><option value="business_support" <?php selected( $contact['role'], 'business_support' ); ?>><?php echo esc_html__( 'Business Support', 'hatnikotni-chat' ); ?></option><option value="partnerships" <?php selected( $contact['role'], 'partnerships' ); ?>><?php echo esc_html__( 'Sales & Partnerships', 'hatnikotni-chat' ); ?></option></select><p class="description"><?php echo esc_html__( 'Assign a routing role. Only active contacts assigned to the selected role are eligible for purpose routing.', 'hatnikotni-chat' ); ?></p>
					</div>
					<div class="hatnch-field">
						<label for="hatnch-description"><?php echo esc_html__( 'Description', 'hatnikotni-chat' ); ?> <span class="hatnch-optional-mark"><?php echo esc_html__( 'Optional', 'hatnikotni-chat' ); ?></span></label>
						<input id="hatnch-description" name="description" type="text" maxlength="255" placeholder="<?php echo esc_attr__( 'Short internal note about this contact.', 'hatnikotni-chat' ); ?>" value="<?php echo esc_attr( $contact['description'] ); ?>">
					</div>
					<div class="hatnch-field">
						<label for="hatnch-sort-order"><?php echo esc_html__( 'Sort Order', 'hatnikotni-chat' ); ?> <span class="hatnch-optional-mark"><?php echo esc_html__( 'Optional', 'hatnikotni-chat' ); ?></span></label>
						<input id="hatnch-sort-order" name="sort_order" type="number" min="0" value="<?php echo esc_attr( $contact['sort_order'] ); ?>">
						<p class="description"><?php echo esc_html__( 'Lower numbers appear first in the admin and round-robin sequence.', 'hatnikotni-chat' ); ?></p>
					</div>
				</div>
				</div><!-- /.hatnch-tab-panel routing -->
				<div class="hatnch-tab-panel" id="hatnch-pane-integrations" role="tabpanel" aria-labelledby="hatnch-tab-integrations" data-hatnch-pane="integrations" hidden>
					<section class="hatnch-panel">
						<div class="hatnch-panel__header"><div><h2><?php echo esc_html__( 'Extension modules', 'hatnikotni-chat' ); ?></h2><p><?php echo esc_html__( 'Optional modules can add site-specific features without changing the core plugin.', 'hatnikotni-chat' ); ?></p></div></div>
						<?php $extension_panels = apply_filters( 'hatnch_admin_extension_panels', array() ); ?>
						<?php if ( is_array( $extension_panels ) && ! empty( $extension_panels ) ) : ?>
							<?php foreach ( $extension_panels as $extension_panel ) : if ( ! is_array( $extension_panel ) || empty( $extension_panel['callback'] ) || ! is_callable( $extension_panel['callback'] ) ) { continue; } ?>
								<section class="hatnch-extension-panel"><h3><?php echo esc_html( $extension_panel['title'] ?? __( 'Extension', 'hatnikotni-chat' ) ); ?></h3><?php call_user_func( $extension_panel['callback'] ); ?></section>
							<?php endforeach; ?>
						<?php else : ?><p class="description"><?php echo esc_html__( 'No extension modules are currently providing settings.', 'hatnikotni-chat' ); ?></p><?php endif; ?>
					</section>
				</div><!-- /.hatnch-tab-panel integrations -->
				<div class="hatnch-form-actions">
					<?php submit_button( $contact['id'] ? __( 'Update Contact', 'hatnikotni-chat' ) : __( 'Add Contact', 'hatnikotni-chat' ), 'primary', '', false ); ?>
					<a class="hatnch-button hatnch-button--ghost" href="<?php echo esc_url( admin_url( 'admin.php?page=hatnch-contacts' ) ); ?>"><?php echo esc_html__( 'Cancel', 'hatnikotni-chat' ); ?></a>
					<label class="hatnch-status-switch">
						<span class="hatnch-status-switch__text"><?php echo esc_html__( 'Status', 'hatnikotni-chat' ); ?></span>
						<input name="status" type="checkbox" role="switch" value="1" <?php checked( 1, (int) $contact['status'] ); ?> aria-label="<?php echo esc_attr__( 'Active contact', 'hatnikotni-chat' ); ?>">
						<span class="hatnch-status-switch__track" aria-hidden="true"></span>
						<span class="hatnch-status-switch__state"><?php echo 1 === (int) $contact['status'] ? esc_html__( 'Active', 'hatnikotni-chat' ) : esc_html__( 'Inactive', 'hatnikotni-chat' ); ?></span>
					</label>
				</div>
			</form>
		</section>
		<?php
	}

	public static function save_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ) );
		}
		check_admin_referer( 'hatnch_save_contact', 'hatnch_nonce' );

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$settings = HATNCH_Settings::all();
		if ( $id > 0 && absint( $settings['default_contact'] ?? 0 ) === $id && ! isset( $_POST['status'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_cannot_deactivate' ) );
			exit;
		}
		$data = array(
			'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'phone'       => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'role'        => isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '',
			'description' => isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '',
			'status'      => isset( $_POST['status'] ) ? 1 : 0,
			'weight'      => isset( $_POST['weight'] ) ? absint( $_POST['weight'] ) : 1,
			'sort_order'  => isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0,
		);

		$result = HATNCH_Contacts::save( $data, $id );
		$url    = is_wp_error( $result ) ? admin_url( 'admin.php?page=hatnch-contacts&error=' . rawurlencode( $result->get_error_code() ) ) : admin_url( 'admin.php?page=hatnch-contacts&updated=1' );
		wp_safe_redirect( $url );
		exit;
	}

	public static function delete_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You do not have permission to delete contacts.', 'hatnikotni-chat' ), 403 ); }
		$id = isset( $_POST['contact_id'] ) ? absint( $_POST['contact_id'] ) : 0;
		$nonce = isset( $_POST['hatnch_delete_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hatnch_delete_nonce'] ) ) : '';
		if ( $id < 1 || ! wp_verify_nonce( $nonce, 'hatnch_delete_contact_' . $id ) ) { wp_die( esc_html__( 'Security check failed. Reload and try again.', 'hatnikotni-chat' ), 403 ); }
		if ( ! HATNCH_Contacts::get( $id ) ) { wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=contact_not_found' ) ); exit; }
		$settings = HATNCH_Settings::all();
		if ( absint( $settings['default_contact'] ?? 0 ) === $id ) { wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_required' ) ); exit; }
		if ( ! HATNCH_Contacts::delete_preserving_analytics( $id ) ) { wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=delete_failed' ) ); exit; }
		wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&deleted=1' ) ); exit;
	}

	/** Assign an active contact as the Default Contact. */
	public static function set_default_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to change the Default Contact.', 'hatnikotni-chat' ), 403 );
		}
		$id    = isset( $_POST['contact_id'] ) ? absint( $_POST['contact_id'] ) : 0;
		$nonce = isset( $_POST['hatnch_default_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hatnch_default_nonce'] ) ) : '';
		if ( $id < 1 || ! wp_verify_nonce( $nonce, 'hatnch_set_default_contact_' . $id ) ) {
			wp_die( esc_html__( 'Security check failed. Reload and try again.', 'hatnikotni-chat' ), 403 );
		}
		$contact = HATNCH_Contacts::get( $id );
		if ( ! $contact ) { wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=contact_not_found' ) ); exit; }
		if ( 1 !== (int) $contact['status'] ) { wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_failed' ) ); exit; }
		$settings = HATNCH_Settings::all();
		$settings['default_contact'] = $id;
		$saved = update_option( 'hatnch_settings', $settings, false );
		if ( ! $saved && absint( HATNCH_Settings::get( 'default_contact', 0 ) ) !== $id ) { wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_failed' ) ); exit; }
		wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&default_changed=1' ) );
		exit;
	}

	public static function toggle_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ), 403 );
		}

		$id    = isset( $_POST['contact_id'] ) ? absint( $_POST['contact_id'] ) : 0;
		$nonce = isset( $_POST['hatnch_toggle_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hatnch_toggle_nonce'] ) ) : '';

		if ( $id < 1 || ! wp_verify_nonce( $nonce, 'hatnch_toggle_contact_' . $id ) ) {
			wp_die( esc_html__( 'Security check failed. Reload and try again.', 'hatnikotni-chat' ), 403 );
		}

		$contact = HATNCH_Contacts::get( $id );
		if ( ! $contact ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=contact_not_found' ) );
			exit;
		}

		$make_active = 1 !== (int) $contact['status'];
		$settings    = HATNCH_Settings::all();
		$is_default  = absint( $settings['default_contact'] ?? 0 ) === $id;

		if ( ! $make_active && $is_default ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_cannot_deactivate' ) );
			exit;
		}

		if ( ! HATNCH_Contacts::set_status( $id, $make_active ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=contact_status_failed' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&updated=1' ) );
		exit;
	}}
