<?php
/**
 * Analytics admin interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Analytics_Admin {

	public static function init(): void {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		}
	}

	public static function register_menu(): void {
		add_submenu_page(
			'hkc',
			__( 'Analytics', 'hatnikotni-chat' ),
			__( 'Analytics', 'hatnikotni-chat' ),
			'manage_options',
			'hkc-analytics',
			array( __CLASS__, 'render' )
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hatnikotni-chat' ) );
		}

		$days    = isset( $_GET['days'] ) ? min( 180, max( 1, absint( $_GET['days'] ) ) ) : 30;
		$summary = HKC_Analytics::get_summary( $days );
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'WhatsApp Interaction Analytics', 'hatnikotni-chat' ); ?></h1>

			<form method="get">
				<input type="hidden" name="page" value="hkc-analytics">
				<label for="hkc-days"><?php echo esc_html__( 'Period', 'hatnikotni-chat' ); ?></label>
				<select id="hkc-days" name="days">
					<?php foreach ( array( 7, 30, 90, 180 ) as $period ) : ?>
						<option value="<?php echo esc_attr( $period ); ?>" <?php selected( $days, $period ); ?>>
							<?php echo esc_html( sprintf( _n( '%d day', '%d days', $period, 'hatnikotni-chat' ), $period ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php submit_button( __( 'Apply', 'hatnikotni-chat' ), 'secondary', '', false ); ?>
			</form>

			<h2><?php echo esc_html__( 'Clicks', 'hatnikotni-chat' ); ?></h2>
			<p><strong><?php echo esc_html( number_format_i18n( $summary['total'] ) ); ?></strong></p>

			<?php self::render_table( __( 'By Device', 'hatnikotni-chat' ), $summary['by_device'], 'device' ); ?>
			<?php self::render_contact_table( $summary['by_contact'] ); ?>
			<?php self::render_table( __( 'By Campaign', 'hatnikotni-chat' ), $summary['by_campaign'], 'utm_campaign' ); ?>

			<p class="description">
				<?php echo esc_html__( 'A click does not confirm that a WhatsApp message was sent. Events are retained for 180 days.', 'hatnikotni-chat' ); ?>
			</p>
		</div>
		<?php
	}

	private static function render_table( string $title, array $rows, string $key ): void {
		echo '<h2>' . esc_html( $title ) . '</h2>';

		if ( empty( $rows ) ) {
			echo '<p>' . esc_html__( 'No data for this period.', 'hatnikotni-chat' ) . '</p>';
			return;
		}
		?>
		<table class="widefat striped" class="hkc-analytics-table">
			<thead><tr><th scope="col"><?php echo esc_html__( 'Value', 'hatnikotni-chat' ); ?></th><th scope="col"><?php echo esc_html__( 'Clicks', 'hatnikotni-chat' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( '' !== (string) ( $row[ $key ] ?? '' ) ? (string) $row[ $key ] : __( 'Unattributed', 'hatnikotni-chat' ) ); ?></td>
					<td><?php echo esc_html( number_format_i18n( (int) $row['total'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	private static function render_contact_table( array $rows ): void {
		echo '<h2>' . esc_html__( 'By Contact', 'hatnikotni-chat' ) . '</h2>';

		if ( empty( $rows ) ) {
			echo '<p>' . esc_html__( 'No data for this period.', 'hatnikotni-chat' ) . '</p>';
			return;
		}
		?>
		<table class="widefat striped" style="max-width:760px;">
			<thead><tr><th scope="col"><?php echo esc_html__( 'Contact', 'hatnikotni-chat' ); ?></th><th><?php echo esc_html__( 'Clicks', 'hatnikotni-chat' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<?php $contact = HKC_Contacts::get( absint( $row['contact_id'] ) ); ?>
				<tr>
					<td><?php echo esc_html( $contact['name'] ?? __( 'Unknown / removed', 'hatnikotni-chat' ) ); ?></td>
					<td><?php echo esc_html( number_format_i18n( (int) $row['total'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
