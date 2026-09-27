<?php
/**
 * WordPress admin interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATC_Admin {

	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_post_hatc_save_contact', array( __CLASS__, 'save_contact' ) );
		add_action( 'admin_post_hatc_toggle_contact', array( __CLASS__, 'toggle_contact' ) );
		add_action( 'admin_post_hatc_save_settings', array( __CLASS__, 'save_settings' ) );
	}

	public static function enqueue_assets( string $hook_suffix ): void {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'toplevel_page_hkc' !== $hook_suffix && ! in_array( $page, array( 'hkc', 'hatc-contacts', 'hatc-analytics' ), true ) ) {
			return;
		}

		wp_enqueue_style(
			'hatc-admin',
			HATC_PLUGIN_URL . 'assets/css/hatnikotni-chat-admin.css',
			array(),
			HATC_VERSION
		);
	}

	public static function register_menu(): void {
		add_menu_page(
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			'manage_options',
			'hkc',
			array( __CLASS__, 'render_general' ),
			'dashicons-format-chat',
			58
		);

		add_submenu_page( 'hkc', __( 'General', 'hatnikotni-chat' ), __( 'General', 'hatnikotni-chat' ), 'manage_options', 'hkc', array( __CLASS__, 'render_general' ) );
		add_submenu_page( 'hkc', __( 'Contacts', 'hatnikotni-chat' ), __( 'Contacts', 'hatnikotni-chat' ), 'manage_options', 'hatc-contacts', array( __CLASS__, 'render_contacts' ) );
	}

	public static function render_general(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hatnikotni-chat' ) );
		}

		$contacts = HATC_Contacts::get_all();
		$settings = HATC_Settings::all();
		?>
		<div class="wrap hatc-admin">
			<header class="hatc-page-header">
				<div>
					<p class="hatc-eyebrow"><?php echo esc_html__( 'WhatsApp contact layer', 'hatnikotni-chat' ); ?></p>
					<h1><?php echo esc_html__( 'Hatnikotni Chat', 'hatnikotni-chat' ); ?></h1>
					<p class="hatc-admin-intro"><?php echo esc_html__( 'Configure how visitors reach your WhatsApp contacts and how the button appears across the site.', 'hatnikotni-chat' ); ?></p>
				</div>
				<div class="hatc-page-header__status">
					<span class="hatc-status-badge <?php echo ! empty( $settings['enabled'] ) ? 'is-active' : 'is-inactive'; ?>">
						<?php echo ! empty( $settings['enabled'] ) ? esc_html__( 'Enabled', 'hatnikotni-chat' ) : esc_html__( 'Disabled', 'hatnikotni-chat' ); ?>
					</span>
				</div>
			</header>

			<?php
			if ( isset( $_GET['updated'] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'hatnikotni-chat' ) . '</p></div>';
			}
			if ( isset( $_GET['error'] ) ) {
				$error_messages = array(
					'missing_name'     => __( 'Contact name is required.', 'hatnikotni-chat' ),
					'invalid_phone'    => __( 'Enter a valid WhatsApp number using international digits only, without +, spaces or hyphens.', 'hatnikotni-chat' ),
					'db_update_failed' => __( 'The contact could not be updated.', 'hatnikotni-chat' ),
					'db_insert_failed' => __( 'The contact could not be created.', 'hatnikotni-chat' ),
				);
				$error_key      = sanitize_key( wp_unslash( $_GET['error'] ) );
				if ( isset( $error_messages[ $error_key ] ) ) {
					echo '<div class="notice notice-error"><p>' . esc_html( $error_messages[ $error_key ] ) . '</p></div>';
				}
			}
			?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hatc-settings-form">
				<input type="hidden" name="action" value="hatc_save_settings">
				<?php wp_nonce_field( 'hatc_save_settings', 'hatc_nonce' ); ?>

				<section class="hatc-panel">
					<div class="hatc-panel__header">
						<div>
							<h2><?php echo esc_html__( 'Core settings', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Control whether the site-wide WhatsApp button is active and which contact receives visitors by default.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php echo esc_html__( 'Enable', 'hatnikotni-chat' ); ?></th>
							<td><label class="hatc-toggle-label"><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>> <span><?php echo esc_html__( 'Enable Hatnikotni Chat frontend button', 'hatnikotni-chat' ); ?></span></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="hatc-default-contact"><?php echo esc_html__( 'Default Contact', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<select id="hatc-default-contact" name="default_contact">
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

				<section class="hatc-panel">
					<div class="hatc-panel__header">
						<div>
							<h2><?php echo esc_html__( 'Button & message', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Set the visitor-facing label, optional pre-filled message and placement of the floating button.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="hatc-default-message"><?php echo esc_html__( 'Default Message', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<textarea id="hatc-default-message" name="default_message" rows="4" class="large-text" maxlength="1000" placeholder="<?php echo esc_attr__( 'Example: Hello, I would like to ask about your services.', 'hatnikotni-chat' ); ?>"><?php echo esc_textarea( $settings['default_message'] ?? '' ); ?></textarea>
								<p class="description"><?php echo esc_html__( 'Optional pre-filled message. Keep it short and relevant to a first contact.', 'hatnikotni-chat' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="hatc-button-label"><?php echo esc_html__( 'Button Label', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<input id="hatc-button-label" name="button_label" type="text" class="regular-text" maxlength="80" placeholder="<?php echo esc_attr__( 'WhatsApp Kami', 'hatnikotni-chat' ); ?>" value="<?php echo esc_attr( $settings['button_label'] ?? 'WhatsApp Kami' ); ?>">
								<p class="description"><?php echo esc_html__( 'Text visitors see on the WhatsApp button.', 'hatnikotni-chat' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="hatc-button-position"><?php echo esc_html__( 'Button Position', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<select id="hatc-button-position" name="button_position">
									<option value="right" <?php selected( $settings['button_position'] ?? 'right', 'right' ); ?>><?php echo esc_html__( 'Bottom right', 'hatnikotni-chat' ); ?></option>
									<option value="left" <?php selected( $settings['button_position'] ?? 'right', 'left' ); ?>><?php echo esc_html__( 'Bottom left', 'hatnikotni-chat' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html__( 'Visibility', 'hatnikotni-chat' ); ?></th>
							<td>
								<div class="hatc-option-list">
									<label class="hatc-toggle-label"><input type="checkbox" name="show_desktop" value="1" <?php checked( ! empty( $settings['show_desktop'] ) ); ?>> <span><?php echo esc_html__( 'Desktop', 'hatnikotni-chat' ); ?></span></label>
									<label class="hatc-toggle-label"><input type="checkbox" name="show_mobile" value="1" <?php checked( ! empty( $settings['show_mobile'] ) ); ?>> <span><?php echo esc_html__( 'Mobile', 'hatnikotni-chat' ); ?></span></label>
								</div>
							</td>
						</tr>
					</table>
				</section>

				<section class="hatc-panel">
					<div class="hatc-panel__header">
						<div>
							<h2><?php echo esc_html__( 'Routing', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Choose how active contacts are selected when a visitor starts a WhatsApp interaction.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="hatc-routing-method"><?php echo esc_html__( 'Routing Method', 'hatnikotni-chat' ); ?></label></th>
							<td>
								<select id="hatc-routing-method" name="routing_method">
									<option value="direct" <?php selected( $settings['routing_method'] ?? 'direct', 'direct' ); ?>><?php echo esc_html__( 'Direct', 'hatnikotni-chat' ); ?></option>
									<option value="random" <?php selected( $settings['routing_method'] ?? 'direct', 'random' ); ?>><?php echo esc_html__( 'Random', 'hatnikotni-chat' ); ?></option>
									<option value="round_robin" <?php selected( $settings['routing_method'] ?? 'direct', 'round_robin' ); ?>><?php echo esc_html__( 'Round robin', 'hatnikotni-chat' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</section>

				<div class="hatc-form-actions">
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

		check_admin_referer( 'hatc_save_settings', 'hatc_nonce' );

		$contact_id = isset( $_POST['default_contact'] ) ? absint( $_POST['default_contact'] ) : 0;
		$contact    = $contact_id > 0 ? HATC_Contacts::get( $contact_id ) : null;
		$method     = isset( $_POST['routing_method'] ) ? sanitize_key( wp_unslash( $_POST['routing_method'] ) ) : 'direct';

		if ( ! in_array( $method, array( 'direct', 'random', 'round_robin' ), true ) ) {
			$method = 'direct';
		}

		$settings = array(
			'enabled'         => isset( $_POST['enabled'] ),
			'default_contact' => $contact && 1 === (int) $contact['status'] ? $contact_id : 0,
			'default_message' => isset( $_POST['default_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['default_message'] ) ) : '',
			'button_label'    => isset( $_POST['button_label'] ) ? sanitize_text_field( wp_unslash( $_POST['button_label'] ) ) : 'WhatsApp Kami',
			'button_position' => isset( $_POST['button_position'] ) && 'left' === sanitize_key( wp_unslash( $_POST['button_position'] ) ) ? 'left' : 'right',
			'show_desktop'    => isset( $_POST['show_desktop'] ),
			'show_mobile'     => isset( $_POST['show_mobile'] ),
			'routing_method'  => $method,
		);

		update_option( 'hatc_settings', $settings, false );

		wp_safe_redirect( admin_url( 'admin.php?page=hkc&updated=1' ) );
		exit;
	}

	public static function render_contacts(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hatnikotni-chat' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin edit selector.
		$edit_id  = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		$editing  = $edit_id ? HATC_Contacts::get( $edit_id ) : null;
		$contacts = HATC_Contacts::get_all();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen selector.
		$is_new = isset( $_GET['action'] ) && 'new' === sanitize_key( wp_unslash( $_GET['action'] ) );
		?>
		<div class="wrap hatc-admin">
			<header class="hatc-page-header">
				<div>
					<p class="hatc-eyebrow"><?php echo esc_html__( 'Contact management', 'hatnikotni-chat' ); ?></p>
					<h1><?php echo esc_html__( 'Contacts', 'hatnikotni-chat' ); ?></h1>
					<p class="hatc-admin-intro"><?php echo esc_html__( 'Manage the people or teams who receive WhatsApp enquiries. Phone numbers use international digits-only format.', 'hatnikotni-chat' ); ?></p>
				</div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=hatc-contacts&action=new' ) ); ?>" class="hatc-button hatc-button--secondary"><?php echo esc_html__( 'Add Contact', 'hatnikotni-chat' ); ?></a>
			</header>

			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice flag. ?>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Contact saved.', 'hatnikotni-chat' ); ?></p></div>
			<?php endif; ?>

			<?php if ( $editing || $is_new ) : ?>
				<?php self::render_contact_form( $editing ); ?>
			<?php endif; ?>

			<section class="hatc-panel hatc-panel--table">
				<div class="hatc-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Contact list', 'hatnikotni-chat' ); ?></h2>
						<p><?php echo esc_html__( 'Inactive contacts remain available for historical analytics and can be activated again later.', 'hatnikotni-chat' ); ?></p>
					</div>
					<span class="hatc-count-badge"><?php echo esc_html( number_format_i18n( count( $contacts ) ) ); ?></span>
				</div>

				<div class="hatc-table-wrap">
					<table class="widefat fixed striped hatc-contacts-table">
						<thead>
							<tr>
								<th scope="col"><?php echo esc_html__( 'Name', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Phone', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Role', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Status', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Weight', 'hatnikotni-chat' ); ?></th>
								<th scope="col"><?php echo esc_html__( 'Actions', 'hatnikotni-chat' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $contacts ) ) : ?>
								<tr><td colspan="6"><?php echo esc_html__( 'No contacts yet.', 'hatnikotni-chat' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $contacts as $contact ) : ?>
									<tr>
										<td>
											<strong><?php echo esc_html( $contact['name'] ); ?></strong>
											<?php if ( ! empty( $contact['description'] ) ) : ?>
												<span class="hatc-table-meta"><?php echo esc_html( $contact['description'] ); ?></span>
											<?php endif; ?>
										</td>
										<td><span class="hatc-mono"><?php echo esc_html( $contact['phone'] ); ?></span></td>
										<td><?php echo esc_html( $contact['role'] ); ?></td>
										<td>
											<span class="hatc-status-badge <?php echo 1 === (int) $contact['status'] ? 'is-active' : 'is-inactive'; ?>">
												<?php echo 1 === (int) $contact['status'] ? esc_html__( 'Active', 'hatnikotni-chat' ) : esc_html__( 'Inactive', 'hatnikotni-chat' ); ?>
											</span>
										</td>
										<td><?php echo esc_html( (string) $contact['weight'] ); ?></td>
										<td>
											<div class="hatc-actions">
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=hatc-contacts&edit=' . (int) $contact['id'] ) ); ?>"><?php echo esc_html__( 'Edit', 'hatnikotni-chat' ); ?></a>
												<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hatc_toggle_contact&id=' . (int) $contact['id'] ), 'hatc_toggle_contact_' . (int) $contact['id'] ) ); ?>">
													<?php echo 1 === (int) $contact['status'] ? esc_html__( 'Deactivate', 'hatnikotni-chat' ) : esc_html__( 'Activate', 'hatnikotni-chat' ); ?>
												</a>
											</div>
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
		<section class="hatc-panel hatc-contact-form">
			<div class="hatc-panel__header">
				<div>
					<h2><?php echo $contact['id'] ? esc_html__( 'Edit Contact', 'hatnikotni-chat' ) : esc_html__( 'Add Contact', 'hatnikotni-chat' ); ?></h2>
					<p><?php echo esc_html__( 'Keep contact details accurate so routing and analytics remain reliable.', 'hatnikotni-chat' ); ?></p>
				</div>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="hatc_save_contact">
				<input type="hidden" name="id" value="<?php echo esc_attr( $contact['id'] ); ?>">
				<?php wp_nonce_field( 'hatc_save_contact', 'hatc_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><label for="hatc-name"><?php echo esc_html__( 'Name', 'hatnikotni-chat' ); ?></label></th><td><input id="hatc-name" name="name" type="text" class="regular-text" maxlength="100" required value="<?php echo esc_attr( $contact['name'] ); ?>"></td></tr>
					<tr><th scope="row"><label for="hatc-phone"><?php echo esc_html__( 'WhatsApp Number', 'hatnikotni-chat' ); ?></label></th><td><input id="hatc-phone" name="phone" type="tel" class="regular-text" maxlength="20" inputmode="numeric" pattern="[0-9]{8,20}" placeholder="<?php echo esc_attr__( '60123456789', 'hatnikotni-chat' ); ?>" required value="<?php echo esc_attr( $contact['phone'] ); ?>"><p class="description"><?php echo esc_html__( 'Use international digits only: country code + number, without +, spaces or hyphens. Example: 60123456789.', 'hatnikotni-chat' ); ?></p></td></tr>
					<tr><th scope="row"><label for="hatc-role"><?php echo esc_html__( 'Role', 'hatnikotni-chat' ); ?></label></th><td><input id="hatc-role" name="role" type="text" class="regular-text" maxlength="100" placeholder="<?php echo esc_attr__( 'Sales, Support, Orders', 'hatnikotni-chat' ); ?>" value="<?php echo esc_attr( $contact['role'] ); ?>"></td></tr>
					<tr><th scope="row"><label for="hatc-description"><?php echo esc_html__( 'Description', 'hatnikotni-chat' ); ?></label></th><td><input id="hatc-description" name="description" type="text" class="regular-text" maxlength="255" placeholder="<?php echo esc_attr__( 'Short internal note about this contact.', 'hatnikotni-chat' ); ?>" value="<?php echo esc_attr( $contact['description'] ); ?>"></td></tr>
					<tr><th scope="row"><label for="hatc-weight"><?php echo esc_html__( 'Weight', 'hatnikotni-chat' ); ?></label></th><td><input id="hatc-weight" name="weight" type="number" min="1" max="65535" value="<?php echo esc_attr( $contact['weight'] ); ?>"><p class="description"><?php echo esc_html__( 'Reserved for future weighted routing. Leave at 1 for V1.', 'hatnikotni-chat' ); ?></p></td></tr>
					<tr><th scope="row"><label for="hatc-sort-order"><?php echo esc_html__( 'Sort Order', 'hatnikotni-chat' ); ?></label></th><td><input id="hatc-sort-order" name="sort_order" type="number" min="0" value="<?php echo esc_attr( $contact['sort_order'] ); ?>"><p class="description"><?php echo esc_html__( 'Controls contact order in the admin and round-robin sequence. Lower numbers appear first.', 'hatnikotni-chat' ); ?></p></td></tr>
					<tr><th scope="row"><?php echo esc_html__( 'Status', 'hatnikotni-chat' ); ?></th><td><label class="hatc-toggle-label"><input name="status" type="checkbox" value="1" <?php checked( 1, (int) $contact['status'] ); ?>> <span><?php echo esc_html__( 'Active', 'hatnikotni-chat' ); ?></span></label></td></tr>
				</table>
				<div class="hatc-form-actions">
					<?php submit_button( $contact['id'] ? __( 'Update Contact', 'hatnikotni-chat' ) : __( 'Add Contact', 'hatnikotni-chat' ), 'primary', '', false ); ?>
					<a class="hatc-button hatc-button--ghost" href="<?php echo esc_url( admin_url( 'admin.php?page=hatc-contacts' ) ); ?>"><?php echo esc_html__( 'Cancel', 'hatnikotni-chat' ); ?></a>
				</div>
			</form>
		</section>
		<?php
	}

	public static function save_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ) );
		}
		check_admin_referer( 'hatc_save_contact', 'hatc_nonce' );

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'phone'       => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'role'        => isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '',
			'description' => isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '',
			'status'      => isset( $_POST['status'] ) ? 1 : 0,
			'weight'      => isset( $_POST['weight'] ) ? absint( $_POST['weight'] ) : 1,
			'sort_order'  => isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0,
		);

		$result = HATC_Contacts::save( $data, $id );
		$url    = is_wp_error( $result ) ? admin_url( 'admin.php?page=hatc-contacts&error=' . rawurlencode( $result->get_error_code() ) ) : admin_url( 'admin.php?page=hatc-contacts&updated=1' );
		wp_safe_redirect( $url );
		exit;
	}

	public static function toggle_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- ID is protected by the following action nonce.
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'hatc_toggle_contact_' . $id );

		if ( $id > 0 ) {
			$contact = HATC_Contacts::get( $id );
			if ( $contact ) {
				HATC_Contacts::set_status( $id, 1 !== (int) $contact['status'] );
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=hatc-contacts&updated=1' ) );
		exit;
	}
}
