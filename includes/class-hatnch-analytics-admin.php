<?php
/**
 * Analytics admin interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Analytics_Admin {

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
			'hatnch-analytics',
			array( __CLASS__, 'render' )
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hatnikotni-chat' ) );
		}

		$days    = isset( $_GET['days'] ) ? min( 180, max( 1, absint( $_GET['days'] ) ) ) : 30;
		$summary = HATNCH_Analytics::get_summary( $days );
		?>
		<div class="wrap hatnch-admin">
			<header class="hatnch-page-header">
				<div>
					<p class="hatnch-eyebrow"><?php echo esc_html__( 'Performance overview', 'hatnikotni-chat' ); ?></p>
					<h1><?php echo esc_html__( 'WhatsApp Interaction Analytics', 'hatnikotni-chat' ); ?></h1>
					<p class="hatnch-admin-intro"><?php echo esc_html__( 'Review WhatsApp button interactions recorded with visitor consent. A click is an interaction event, not proof that a message was sent.', 'hatnikotni-chat' ); ?></p>
				</div>
			</header>

			<div class="hatnch-analytics-top">
				<section class="hatnch-stat-grid" aria-label="<?php echo esc_attr__( 'Analytics summary', 'hatnikotni-chat' ); ?>">
					<div class="hatnch-stat-card hatnch-stat-card--accent">
						<span class="hatnch-stat-card__label"><?php echo esc_html__( 'WhatsApp clicks', 'hatnikotni-chat' ); ?></span>
						<strong class="hatnch-stat-card__value"><?php echo esc_html( number_format_i18n( $summary['total'] ) ); ?></strong>
						<?php // Translators: %d is the number of days in the selected analytics period. ?>
						<span class="hatnch-stat-card__meta"><?php echo esc_html( sprintf( _n( 'Last %d day', 'Last %d days', $days, 'hatnikotni-chat' ), $days ) ); ?></span>
					</div>
					<div class="hatnch-stat-card">
						<span class="hatnch-stat-card__label"><?php echo esc_html__( 'Retention', 'hatnikotni-chat' ); ?></span>
						<strong class="hatnch-stat-card__value"><?php echo esc_html__( '180 days', 'hatnikotni-chat' ); ?></strong>
						<span class="hatnch-stat-card__meta"><?php echo esc_html__( 'Local database events', 'hatnikotni-chat' ); ?></span>
					</div>
				</section>

				<section class="hatnch-panel hatnch-filter-panel">
					<form method="get" class="hatnch-filter-form">
						<input type="hidden" name="page" value="hatnch-analytics">
						<div>
							<label for="hatnch-days"><?php echo esc_html__( 'Reporting period', 'hatnikotni-chat' ); ?></label>
							<select id="hatnch-days" name="days">
								<?php foreach ( array( 7, 30, 90, 180 ) as $period ) : ?>
									<option value="<?php echo esc_attr( $period ); ?>" <?php selected( $days, $period ); ?>>
										<?php
										// Translators: %d is the number of days in the selected analytics period.
										echo esc_html( sprintf( _n( '%d day', '%d days', $period, 'hatnikotni-chat' ), $period ) );
										?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<?php submit_button( __( 'Apply', 'hatnikotni-chat' ), 'secondary', '', false ); ?>
					</form>
				</section>
			</div>

			<div class="hatnch-data-grid">
				<section class="hatnch-panel hatnch-panel--table">
					<div class="hatnch-panel__header">
						<div>
							<h2><?php echo esc_html__( 'By Device', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Broad device categories only.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<?php self::render_table( $summary['by_device'], 'device' ); ?>
				</section>

				<section class="hatnch-panel hatnch-panel--table">
					<div class="hatnch-panel__header">
						<div>
							<h2><?php echo esc_html__( 'By Contact', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Clicks attributed to each contact.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<?php self::render_contact_table( $summary['by_contact'] ); ?>
				</section>

				<section class="hatnch-panel hatnch-panel--table hatnch-data-grid__wide">
					<div class="hatnch-panel__header">
						<div>
							<h2><?php echo esc_html__( 'By Campaign', 'hatnikotni-chat' ); ?></h2>
							<p><?php echo esc_html__( 'Last-touch UTM campaign attribution when consent is available.', 'hatnikotni-chat' ); ?></p>
						</div>
					</div>
					<?php self::render_table( $summary['by_campaign'], 'utm_campaign' ); ?>
				</section>
			</div>

			<p class="hatnch-admin-note"><?php echo esc_html__( 'A click does not confirm that a WhatsApp message was sent. Analytics and campaign attribution require visitor consent.', 'hatnikotni-chat' ); ?></p>
		</div>
		<?php
	}

	private static function render_table( array $rows, string $key ): void {
		if ( empty( $rows ) ) {
			echo '<p class="hatnch-empty-state">' . esc_html__( 'No data for this period.', 'hatnikotni-chat' ) . '</p>';
			return;
		}
		?>
		<div class="hatnch-table-wrap">
			<table class="widefat striped hatnch-analytics-table">
				<thead>
					<tr>
						<th scope="col"><?php echo esc_html__( 'Value', 'hatnikotni-chat' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Clicks', 'hatnikotni-chat' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( '' !== (string) ( $row[ $key ] ?? '' ) ? (string) $row[ $key ] : __( 'Unattributed', 'hatnikotni-chat' ) ); ?></td>
						<td><strong><?php echo esc_html( number_format_i18n( (int) $row['total'] ) ); ?></strong></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	private static function render_contact_table( array $rows ): void {
		if ( empty( $rows ) ) {
			echo '<p class="hatnch-empty-state">' . esc_html__( 'No data for this period.', 'hatnikotni-chat' ) . '</p>';
			return;
		}
		?>
		<div class="hatnch-table-wrap">
			<table class="widefat striped hatnch-analytics-table">
				<thead>
					<tr>
						<th scope="col"><?php echo esc_html__( 'Contact', 'hatnikotni-chat' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Clicks', 'hatnikotni-chat' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<?php $contact = HATNCH_Contacts::get( absint( $row['contact_id'] ) ); ?>
					<tr>
						<td><?php echo esc_html( $contact['name'] ?? __( 'Unknown / removed', 'hatnikotni-chat' ) ); ?></td>
						<td><strong><?php echo esc_html( number_format_i18n( (int) $row['total'] ) ); ?></strong></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
