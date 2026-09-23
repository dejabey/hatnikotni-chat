<?php
/**
 * WordPress admin interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Admin {

	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_hkc_save_contact', array( __CLASS__, 'save_contact' ) );
		add_action( 'admin_post_hkc_toggle_contact', array( __CLASS__, 'toggle_contact' ) );
	}

	public static function register_menu(): void {
		add_menu_page(
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			'manage_options',
			'hkc',
			array( __CLASS__, 'render_contacts' ),
			'dashicons-format-chat',
			58
		);

		add_submenu_page(
			'hkc',
			__( 'Contacts', 'hatnikotni-chat' ),
			__( 'Contacts', 'hatnikotni-chat' ),
			'manage_options',
			'hkc',
			array( __CLASS__, 'render_contacts' )
		);
	}

	public static function render_contacts(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hatnikotni-chat' ) );
		}

		$edit_id  = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		$editing  = $edit_id ? HKC_Contacts::get( $edit_id ) : null;
		$contacts = HKC_Contacts::get_all();
		$is_new   = isset( $_GET['action'] ) && 'new' === sanitize_key( wp_unslash( $_GET['action'] ) );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html__( 'Contacts', 'hatnikotni-chat' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=hkc&action=new' ) ); ?>" class="page-title-action">
				<?php echo esc_html__( 'Add New', 'hatnikotni-chat' ); ?>
			</a>
			<hr class="wp-header-end">

			<?php self::render_notice(); ?>

			<?php if ( $editing || $is_new ) : ?>
				<?php self::render_contact_form( $editing ); ?>
			<?php endif; ?>

			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Name', 'hatnikotni-chat' ); ?></th>
						<th><?php echo esc_html__( 'Phone', 'hatnikotni-chat' ); ?></th>
						<th><?php echo esc_html__( 'Role', 'hatnikotni-chat' ); ?></th>
						<th><?php echo esc_html__( 'Status', 'hatnikotni-chat' ); ?></th>
						<th><?php echo esc_html__( 'Weight', 'hatnikotni-chat' ); ?></th>
						<th><?php echo esc_html__( 'Actions', 'hatnikotni-chat' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $contacts ) ) : ?>
					<tr><td colspan="6"><?php echo esc_html__( 'No contacts yet.', 'hatnikotni-chat' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $contacts as $contact ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $contact['name'] ); ?></strong></td>
							<td><?php echo esc_html( $contact['phone'] ); ?></td>
							<td><?php echo esc_html( $contact['role'] ); ?></td>
							<td><?php echo 1 === (int) $contact['status'] ? esc_html__( 'Active', 'hatnikotni-chat' ) : esc_html__( 'Inactive', 'hatnikotni-chat' ); ?></td>
							<td><?php echo esc_html( (string) $contact['weight'] ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=hkc&edit=' . (int) $contact['id'] ) ); ?>">
									<?php echo esc_html__( 'Edit', 'hatnikotni-chat' ); ?>
								</a>
								|
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hkc_toggle_contact&id=' . (int) $contact['id'] ), 'hkc_toggle_contact_' . (int) $contact['id'] ) ); ?>">
									<?php echo 1 === (int) $contact['status'] ? esc_html__( 'Deactivate', 'hatnikotni-chat' ) : esc_html__( 'Activate', 'hatnikotni-chat' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
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
		<div style="max-width:760px;margin:20px 0;">
			<h2><?php echo $contact['id'] ? esc_html__( 'Edit Contact', 'hatnikotni-chat' ) : esc_html__( 'Add Contact', 'hatnikotni-chat' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="hkc_save_contact">
				<input type="hidden" name="id" value="<?php echo esc_attr( $contact['id'] ); ?>">
				<?php wp_nonce_field( 'hkc_save_contact', 'hkc_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="hkc-name"><?php echo esc_html__( 'Name', 'hatnikotni-chat' ); ?></label></th>
						<td><input id="hkc-name" name="name" type="text" class="regular-text" maxlength="100" required value="<?php echo esc_attr( $contact['name'] ); ?>"></td>
					</tr>
					<tr>
						<th><label for="hkc-phone"><?php echo esc_html__( 'WhatsApp Number', 'hatnikotni-chat' ); ?></label></th>
						<td><input id="hkc-phone" name="phone" type="tel" class="regular-text" maxlength="30" required value="<?php echo esc_attr( $contact['phone'] ); ?>"><p class="description"><?php echo esc_html__( 'Country code included. Example: 60123456789.', 'hatnikotni-chat' ); ?></p></td>
					</tr>
					<tr>
						<th><label for="hkc-role"><?php echo esc_html__( 'Role', 'hatnikotni-chat' ); ?></label></th>
						<td><input id="hkc-role" name="role" type="text" class="regular-text" maxlength="100" value="<?php echo esc_attr( $contact['role'] ); ?>"></td>
					</tr>
					<tr>
						<th><label for="hkc-description"><?php echo esc_html__( 'Description', 'hatnikotni-chat' ); ?></label></th>
						<td><input id="hkc-description" name="description" type="text" class="regular-text" maxlength="255" value="<?php echo esc_attr( $contact['description'] ); ?>"></td>
					</tr>
					<tr>
						<th><label for="hkc-weight"><?php echo esc_html__( 'Weight', 'hatnikotni-chat' ); ?></label></th>
						<td><input id="hkc-weight" name="weight" type="number" min="1" max="65535" value="<?php echo esc_attr( $contact['weight'] ); ?>"></td>
					</tr>
					<tr>
						<th><label for="hkc-sort-order"><?php echo esc_html__( 'Sort Order', 'hatnikotni-chat' ); ?></label></th>
						<td><input id="hkc-sort-order" name="sort_order" type="number" min="0" value="<?php echo esc_attr( $contact['sort_order'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php echo esc_html__( 'Status', 'hatnikotni-chat' ); ?></th>
						<td><label><input name="status" type="checkbox" value="1" <?php checked( 1, (int) $contact['status'] ); ?>> <?php echo esc_html__( 'Active', 'hatnikotni-chat' ); ?></label></td>
					</tr>
				</table>
				<?php submit_button( $contact['id'] ? __( 'Update Contact', 'hatnikotni-chat' ) : __( 'Add Contact', 'hatnikotni-chat' ) ); ?>
			</form>
		</div>
		<?php
	}

	public static function save_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ) );
		}

		check_admin_referer( 'hkc_save_contact', 'hkc_nonce' );

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'name'        => isset( $_POST['name'] ) ? wp_unslash( $_POST['name'] ) : '',
			'phone'       => isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '',
			'role'        => isset( $_POST['role'] ) ? wp_unslash( $_POST['role'] ) : '',
			'description' => isset( $_POST['description'] ) ? wp_unslash( $_POST['description'] ) : '',
			'status'      => isset( $_POST['status'] ) ? 1 : 0,
			'weight'      => isset( $_POST['weight'] ) ? absint( $_POST['weight'] ) : 1,
			'sort_order'  => isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0,
		);

		$result = HKC_Contacts::save( $data, $id );

		if ( is_wp_error( $result ) ) {
			$url = admin_url( 'admin.php?page=hkc&error=' . rawurlencode( $result->get_error_code() ) );
		} else {
			$url = admin_url( 'admin.php?page=hkc&updated=1' );
		}

		wp_safe_redirect( $url );
		exit;
	}

	public static function toggle_contact(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ) );
		}

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'hkc_toggle_contact_' . $id );

		if ( $id > 0 ) {
			$contact = HKC_Contacts::get( $id );
			if ( $contact ) {
				HKC_Contacts::set_status( $id, 1 !== (int) $contact['status'] );
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=hkc&updated=1' ) );
		exit;
	}

	private static function render_notice(): void {
		if ( isset( $_GET['updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Contact saved.', 'hatnikotni-chat' ) . '</p></div>';
		}

		if ( isset( $_GET['error'] ) ) {
			$messages = array(
				'missing_name'     => __( 'Contact name is required.', 'hatnikotni-chat' ),
				'invalid_phone'    => __( 'Please enter a valid WhatsApp number.', 'hatnikotni-chat' ),
				'db_update_failed' => __( 'The contact could not be updated.', 'hatnikotni-chat' ),
				'db_insert_failed' => __( 'The contact could not be created.', 'hatnikotni-chat' ),
			);
			$key = sanitize_key( wp_unslash( $_GET['error'] ) );
			if ( isset( $messages[ $key ] ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $messages[ $key ] ) . '</p></div>';
			}
		}
	}
}
