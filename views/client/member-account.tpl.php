<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/* @var MPT_Member $member_object */
$member_object       = $member_data['member'];
$pages_options[]     = MPT_Options::get_option_value( 'mpt-pages', 'page-change-profile' );
$pages_options[]     = MPT_Options::get_option_value( 'mpt-pages', 'page-change-password' );
$my_roles            = $member_object->get_roles( 'term_id' );

$last_login_activity = $member_data['last_activity_data'] ?? [];
$args                = [];
$meta_key            = [];
if ( ! empty( $my_roles ) ) {
	foreach ( $my_roles as $role_id ) {
		$meta_key[] = [
			'key'     => '_mpt_role',
			'value'   => (string) $role_id,
			'compare' => 'LIKE',
		];
	}

	$args = [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'no_found_rows'  => true,
		'meta_query'     => wp_parse_args(
			$meta_key,
			[
				'relation' => 'OR',
			]
		),
	];

	/**
	 * Filter query args use to retrieve member pages.
	 *
	 * @param array $args The query args.
	 * @param array $my_roles List of roles term_id.
	 * @param MPT_Member $member_object The member model instance.
	 */
	$args = apply_filters( 'mpt_view_account_pages_query_args', $args, $my_roles, $member_object );
}

$pages_query = new \WP_Query( $args );
?>
	<h2><?php esc_html_e( 'Your resources', 'mpt' ); ?></h2>
<?php if ( $pages_query->have_posts() ) : ?>
	<ul>
		<?php
		while ( $pages_query->have_posts() ) :
			$pages_query->the_post();
			?>
			<li><a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></li>
		<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
<?php else : ?>
	<p><?php printf( __( 'No page associated with your role has been found.', 'mpt' ) ); ?></p>
<?php endif; ?>
	<h2><?php esc_html_e( 'Account', 'mpt' ); ?></h2>
	<ul>
		<?php foreach ( array_filter( $pages_options ) as $option_id ) : ?>
			<li><a href="<?php echo esc_url( get_permalink( $option_id ) ); ?>"><?php echo esc_html( get_the_title( $option_id ) ); ?></a></li>
		<?php endforeach; ?>
	</ul>

<?php if ( ! empty( $last_login_activity ) ) : ?>
	<div class="mpt-field notice-info">
		<h2><?php echo esc_html_e( 'Last login details', 'mpt' ); ?></h2>
		<br>
		<p><strong><?php echo esc_html( $member_data['message'] ); ?></strong></p>
		<br>
		<?php
		foreach ( $last_login_activity as $key => $data ) :
			$label = $data['label'] ?? '';
			$value = $data['value'] ?? '';
			if ( empty( $label ) || empty( $value ) ) {
				continue;
			}

			printf( '<p><strong>%s :</strong> <em>%s</em> </p>', esc_html( $label ), esc_html( $value ) );
		endforeach;
		?>
	</div>
<?php endif; ?>
