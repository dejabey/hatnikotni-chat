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
			add_action( 'admin_post_hatnch_export_analytics', array( __CLASS__, 'export_csv' ) );
			add_action( 'admin_post_hatnch_delete_analytics_range', array( __CLASS__, 'delete_range' ) );
			add_action( 'admin_post_hatnch_delete_analytics_all', array( __CLASS__, 'delete_all' ) );
		}
	}

	public static function register_menu(): void {
		add_submenu_page(
			'hatnch',
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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only analytics filter, bounded to 1–180 days.
		$days    = isset( $_GET['days'] ) ? min( 180, max( 1, absint( $_GET['days'] ) ) ) : 30;
		$summary = HATNCH_Analytics::get_summary( $days );
		?>
		<div class="wrap hatnch-admin hatnch-admin--analytics">
			<header class="hatnch-page-header">
				<div>
					<p class="hatnch-eyebrow"><?php echo esc_html__( 'Performance overview', 'hatnikotni-chat' ); ?></p>
					<h1><?php echo esc_html__( 'WhatsApp Interaction Analytics', 'hatnikotni-chat' ); ?></h1>
					<p class="hatnch-admin-intro"><?php echo esc_html__( 'Review WhatsApp button interactions recorded with visitor consent. A click is an interaction event, not proof that a message was sent.', 'hatnikotni-chat' ); ?></p>
				</div>
			</header>
			<?php HATNCH_Admin::render_workspace_nav( 'analytics' ); ?>

			<?php self::render_action_notice(); ?>

			<div class="hatnch-analytics-top">
				<!-- Summary cards are direct grid items so all three dashboard columns can size evenly. -->
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

			<section class="hatnch-panel hatnch-analytics-management" aria-labelledby="hatnch-analytics-management-title">
				<div class="hatnch-panel__header"><div><h2 id="hatnch-analytics-management-title"><?php echo esc_html__( 'Analytics Data Management', 'hatnikotni-chat' ); ?></h2><p><?php echo esc_html__( 'Export aggregate reports or remove analytics records. These actions do not change contacts or routing settings.', 'hatnikotni-chat' ); ?></p></div></div>
				<div class="hatnch-analytics-management__section hatnch-analytics-management__section--export">
					<h3><?php echo esc_html__( 'Export Reports', 'hatnikotni-chat' ); ?></h3>
					<p><?php echo esc_html__( 'Download an aggregate CSV report for the selected reporting period. Contact phone numbers and raw event identifiers are not included.', 'hatnikotni-chat' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="hatnch_export_analytics">
						<?php wp_nonce_field( 'hatnch_export_analytics' ); ?>
						<label for="hatnch-export-days"><?php echo esc_html__( 'Reporting period', 'hatnikotni-chat' ); ?></label>
						<select id="hatnch-export-days" name="days">
							<?php foreach ( array( 7, 30, 90, 180 ) as $period ) : ?>
								<option value="<?php echo esc_attr( $period ); ?>" <?php selected( $days, $period ); ?>>
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d: number of days in the reporting period. */
											_n( '%d day', '%d days', $period, 'hatnikotni-chat' ),
											$period
										)
									);
									?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php submit_button( __( 'Export CSV', 'hatnikotni-chat' ), 'secondary', 'submit', false ); ?>
					</form>
				</div>
				<div class="hatnch-analytics-management__section hatnch-analytics-management__section--range">
					<h3><?php echo esc_html__( 'Delete Analytics Records', 'hatnikotni-chat' ); ?></h3>
					<p><?php echo esc_html__( 'Delete all analytics events within an inclusive date range using the WordPress site timezone. This cannot be undone.', 'hatnikotni-chat' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="hatnch_delete_analytics_range">
						<?php wp_nonce_field( 'hatnch_delete_analytics_range' ); ?>
						<p><label for="hatnch-delete-start"><?php echo esc_html__( 'Start date', 'hatnikotni-chat' ); ?></label> <input type="date" id="hatnch-delete-start" name="start_date" required></p>
						<p><label for="hatnch-delete-end"><?php echo esc_html__( 'End date', 'hatnikotni-chat' ); ?></label> <input type="date" id="hatnch-delete-end" name="end_date" required></p>
						<p><label><input type="checkbox" name="confirm_range" value="1" required> <?php echo esc_html__( 'I understand these analytics records will be permanently deleted.', 'hatnikotni-chat' ); ?></label></p>
						<?php submit_button( __( 'Delete records in date range', 'hatnikotni-chat' ), 'delete', 'submit', false ); ?>
					</form>
				</div>
				<div class="hatnch-analytics-management__section hatnch-analytics-management__section--all">
					<h3><?php echo esc_html__( 'Delete All Analytics', 'hatnikotni-chat' ); ?></h3>
					<p><?php echo esc_html__( 'Permanently remove every analytics event. Contacts, routing configuration and plugin settings will remain unchanged.', 'hatnikotni-chat' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="hatnch_delete_analytics_all">
						<?php wp_nonce_field( 'hatnch_delete_analytics_all' ); ?>
						<p><label for="hatnch-delete-all-confirm"><?php echo esc_html__( 'Type DELETE to confirm', 'hatnikotni-chat' ); ?></label> <input type="text" id="hatnch-delete-all-confirm" name="confirm_text" autocomplete="off" required></p>
						<?php submit_button( __( 'Delete all analytics records', 'hatnikotni-chat' ), 'delete', 'submit', false ); ?>
					</form>
				</div>
			</section>

			<p class="hatnch-admin-note"><?php echo esc_html__( 'A click does not confirm that a WhatsApp message was sent. Analytics and campaign attribution require visitor consent.', 'hatnikotni-chat' ); ?></p>
		</div>
		<?php
	}

	private static function render_action_notice(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only an opaque outcome code after a POST/redirect/GET action.
		$notice  = isset( $_GET['hatnch_notice'] ) ? sanitize_key( wp_unslash( $_GET['hatnch_notice'] ) ) : '';
		$allowed = array(
			'exported'      => __( 'Analytics report exported.', 'hatnikotni-chat' ),
			'deleted_range' => __( 'Analytics records in the selected date range were deleted.', 'hatnikotni-chat' ),
			'deleted_all'   => __( 'All analytics records were deleted.', 'hatnikotni-chat' ),
			'error'         => __( 'The requested analytics action could not be completed. No success was reported.', 'hatnikotni-chat' ),
		);
		if ( isset( $allowed[ $notice ] ) ) {
			$class = 'error' === $notice ? 'notice-error' : 'notice-success';
			printf( '<div class="notice %1$s is-dismissible"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $allowed[ $notice ] ) );
		}
	}

	private static function authorize_action(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'hatnikotni-chat' ), '', array( 'response' => 403 ) );
		}
	}

	public static function export_csv(): void {
		self::authorize_action();
		check_admin_referer( 'hatnch_export_analytics' );
		$days = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;
		if ( ! in_array( $days, array( 7, 30, 90, 180 ), true ) ) {
			wp_die( esc_html__( 'Invalid reporting period.', 'hatnikotni-chat' ), '', array( 'response' => 400 ) );
		}
		$rows = HATNCH_Analytics::get_export_rows( $days );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="hatnikotni-chat-analytics-' . gmdate( 'Y-m-d' ) . '.csv"' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- CSV is streamed directly to the HTTP response; WP_Filesystem is not applicable to php://output.
		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			wp_die( esc_html__( 'Could not create the report export.', 'hatnikotni-chat' ) );
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Write the UTF-8 BOM to the response stream, not a filesystem path.
		fwrite( $output, "\xEF\xBB\xBF" );
		fputcsv( $output, array( 'Section', 'Value', 'Clicks' ) );
		foreach ( $rows as $row ) {
			$value = (string) $row['value'];
			if ( preg_match( '/^[\s\x00-\x1F]*[=+@\-]/u', $value ) ) {
				$value = "'" . $value;
			}
			fputcsv( $output, array( $row['section'], $value, is_numeric( $row['clicks'] ) ? (int) $row['clicks'] : '' ) );
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Close the HTTP response stream opened above.
		fclose( $output );
		exit;
	}

	public static function delete_range(): void {
		self::authorize_action();
		check_admin_referer( 'hatnch_delete_analytics_range' );
		$confirmed = isset( $_POST['confirm_range'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['confirm_range'] ) );
		$start     = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end       = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$range     = HATNCH_Analytics::validate_date_range( $start, $end );
		if ( ! $confirmed || is_wp_error( $range ) || is_wp_error( HATNCH_Analytics::count_events( $range ) ) ) {
			self::redirect_notice( 'error' );
		}
		$result = HATNCH_Analytics::delete_events( $range );
		self::redirect_notice( is_wp_error( $result ) ? 'error' : 'deleted_range' );
	}

	public static function delete_all(): void {
		self::authorize_action();
		check_admin_referer( 'hatnch_delete_analytics_all' );
		$confirmation = isset( $_POST['confirm_text'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_text'] ) ) : '';
		if ( 'DELETE' !== $confirmation || is_wp_error( HATNCH_Analytics::count_events() ) ) {
			self::redirect_notice( 'error' );
		}
		$result = HATNCH_Analytics::delete_events();
		self::redirect_notice( is_wp_error( $result ) ? 'error' : 'deleted_all' );
	}

	private static function redirect_notice( string $notice ): void {
		$url = add_query_arg(
			array(
				'page'           => 'hatnch-analytics',
				'hatnch_notice'  => $notice,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $url );
		exit;
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
					<?php
					$reference = absint( $row['contact_reference'] ?? 0 );
					$contact   = $reference ? HATNCH_Contacts::get( $reference ) : null;
					$label     = $contact['name'] ?? ( $reference ? sprintf(
						/* translators: %d: ID of a contact that no longer exists. */
						__( 'Removed contact #%d', 'hatnikotni-chat' ),
						$reference
					) : __( 'Unknown / removed', 'hatnikotni-chat' ) );
					?>
					<tr>
						<td><?php echo esc_html( $label ); ?></td>
						<td><strong><?php echo esc_html( number_format_i18n( (int) $row['total'] ) ); ?></strong></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
