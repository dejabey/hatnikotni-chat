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
			'general'   => array(
				'label' => __( 'General', 'hatnikotni-chat' ),
				'url'   => admin_url( 'admin.php?page=hatnch' ),
			),
			'contacts'  => array(
				'label' => __( 'Contacts', 'hatnikotni-chat' ),
				'url'   => admin_url( 'admin.php?page=hatnch-contacts' ),
			),
			'analytics' => array(
				'label' => __( 'Analytics', 'hatnikotni-chat' ),
				'url'   => admin_url( 'admin.php?page=hatnch-analytics' ),
			),
		);
		echo '<nav class="hatnch-workspace-nav" aria-label="' . esc_attr__( 'Hatnikotni Chat workspace', 'hatnikotni-chat' ) . '">';
		foreach ( $items as $key => $item ) {
			$class = 'hatnch-workspace-nav__link' . ( $active === $key ? ' is-active' : '' );
			$aria  = $active === $key ? ' aria-current="page"' : '';
			echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $item['url'] ) . '"' . esc_attr( $aria ) . '>' . esc_html( $item['label'] ) . '</a>';
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
									<?php
									$contact_id = absint( $contact['id'] );
									$is_default = $contact_id === $default_contact_id;
									$set_default_confirmation = sprintf(
										/* translators: %1$s: contact name. */
										__( 'Set %1$s as the Default Contact? New Direct-routing interactions will go to %1$s. The previous Default Contact will remain active.', 'hatnikotni-chat' ),
										$contact['name']
									);
									$delete_confirmation = __( 'Permanently delete this contact? This cannot be undone. Historical analytics events will be retained but anonymized for this contact.', 'hatnikotni-chat' );
									?>
									<tr>
										<td><span class="hatnch-contact-id"><?php echo esc_html( (string) $contact_id ); ?></span></td>
										<td>
											<?php if ( $is_default ) : ?>
												<span class="hatnch-default-badge"><?php echo esc_html__( 'Default', 'hatnikotni-chat' ); ?></span>
											<?php endif; ?>
											<strong class="hatnch-contact-name" title="<?php echo esc_attr( $contact['name'] ); ?>"><?php echo esc_html( $contact['name'] ); ?></strong>
											<?php if ( ! empty( $contact['description'] ) ) : ?>
												<span class="hatnch-table-meta"><?php echo esc_html( $contact['description'] ); ?></span>
											<?php endif; ?>
											<div class="hatnch-row-actions">
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=hatnch-contacts&edit=' . $contact_id ) ); ?>"><?php echo esc_html__( 'Edit', 'hatnikotni-chat' ); ?></a>
												<?php if ( ! $is_default && 1 === (int) $contact['status'] ) : ?>
													<span aria-hidden="true">|</span>
													<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hatnch-row-action-form" onsubmit="return window.confirm(<?php echo esc_attr( wp_json_encode( $set_default_confirmation ) ); ?>);">
														<input type="hidden" name="action" value="hatnch_set_default_contact">
														<input type="hidden" name="contact_id" value="<?php echo esc_attr( (string) $contact_id ); ?>">
														<?php wp_nonce_field( 'hatnch_set_default_contact_' . $contact_id, 'hatnch_default_nonce' ); ?>
														<button type="submit" class="hatnch-row-action-link"><?php echo esc_html__( 'Set as Default', 'hatnikotni-chat' ); ?></button>
													</form>
												<?php endif; ?>
												<?php if ( ! $is_default ) : ?>
													<span aria-hidden="true">|</span>
													<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hatnch-row-action-form">
														<input type="hidden" name="action" value="hatnch_toggle_contact">
														<input type="hidden" name="contact_id" value="<?php echo esc_attr( (string) $contact_id ); ?>">
														<?php wp_nonce_field( 'hatnch_toggle_contact_' . $contact_id, 'hatnch_toggle_nonce' ); ?>
														<button type="submit" class="hatnch-row-action-link"><?php echo 1 === (int) $contact['status'] ? esc_html__( 'Deactivate', 'hatnikotni-chat' ) : esc_html__( 'Activate', 'hatnikotni-chat' ); ?></button>
													</form>
													<span aria-hidden="true">|</span>
													<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return window.confirm(<?php echo esc_attr( wp_json_encode( $delete_confirmation ) ); ?>);">
														<input type="hidden" name="action" value="hatnch_delete_contact">
														<input type="hidden" name="contact_id" value="<?php echo esc_attr( (string) $contact_id ); ?>">
														<?php wp_nonce_field( 'hatnch_delete_contact_' . $contact_id, 'hatnch_delete_nonce' ); ?>
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
							<?php foreach ( $extension_panels as $extension_panel ) : ?>
								<?php
								if ( ! is_array( $extension_panel ) || empty( $extension_panel['callback'] ) || ! is_callable( $extension_panel['callback'] ) ) {
									continue;
								}
								?>
								<section class="hatnch-extension-panel">
									<h3><?php echo esc_html( $extension_panel['title'] ?? __( 'Extension', 'hatnikotni-chat' ) ); ?></h3>
									<?php call_user_func( $extension_panel['callback'] ); ?>
								</section>
							<?php endforeach; ?>
						<?php else : ?>
							<p class="description"><?php echo esc_html__( 'No extension modules are currently providing settings.', 'hatnikotni-chat' ); ?></p>
						<?php endif; ?>
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
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(  esc_html__( 'You do not have permission to delete contacts.', 'hatnikotni-chat' ), '', array( 'response' => 403 ) );
		}
		$id = isset( $_POST['contact_id'] ) ? absint( $_POST['contact_id'] ) : 0;
		$nonce = isset( $_POST['hatnch_delete_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hatnch_delete_nonce'] ) ) : '';
		if ( $id < 1 || ! wp_verify_nonce( $nonce, 'hatnch_delete_contact_' . $id ) ) {
			wp_die(  esc_html__( 'Security check failed. Reload and try again.', 'hatnikotni-chat' ), '', array( 'response' => 403 ) );
		}
		if ( ! HATNCH_Contacts::get( $id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=contact_not_found' ) );
			exit;
		}
		$settings = HATNCH_Settings::all();
		if ( absint( $settings['default_contact'] ?? 0 ) === $id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_required' ) );
			exit;
		}
		if ( ! HATNCH_Contacts::delete_preserving_analytics( $id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=delete_failed' ) );
			exit;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&deleted=1' ) );
		exit;
	}

	/** Assign an active contact as the Default Contact. */
	public static function set_default_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(  esc_html__( 'You do not have permission to change the Default Contact.', 'hatnikotni-chat' ), '', array( 'response' => 403 ) );
		}
		$id    = isset( $_POST['contact_id'] ) ? absint( $_POST['contact_id'] ) : 0;
		$nonce = isset( $_POST['hatnch_default_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hatnch_default_nonce'] ) ) : '';
		if ( $id < 1 || ! wp_verify_nonce( $nonce, 'hatnch_set_default_contact_' . $id ) ) {
			wp_die(  esc_html__( 'Security check failed. Reload and try again.', 'hatnikotni-chat' ), '', array( 'response' => 403 ) );
		}
		$contact = HATNCH_Contacts::get( $id );
		if ( ! $contact ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=contact_not_found' ) );
			exit;
		}
		if ( 1 !== (int) $contact['status'] ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_failed' ) );
			exit;
		}
		$settings = HATNCH_Settings::all();
		$settings['default_contact'] = $id;
		$saved = update_option( 'hatnch_settings', $settings, false );
		if ( ! $saved && absint( HATNCH_Settings::get( 'default_contact', 0 ) ) !== $id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&error=default_contact_failed' ) );
			exit;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=hatnch-contacts&default_changed=1' ) );
		exit;
	}

	public static function toggle_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(  esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ), '', array( 'response' => 403 ) );
		}

		$id    = isset( $_POST['contact_id'] ) ? absint( $_POST['contact_id'] ) : 0;
		$nonce = isset( $_POST['hatnch_toggle_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hatnch_toggle_nonce'] ) ) : '';

		if ( $id < 1 || ! wp_verify_nonce( $nonce, 'hatnch_toggle_contact_' . $id ) ) {
			wp_die(  esc_html__( 'Security check failed. Reload and try again.', 'hatnikotni-chat' ), '', array( 'response' => 403 ) );
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
	}
}
