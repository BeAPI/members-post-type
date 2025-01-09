<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$pages_options[] = MPT_Options::get_option_value( 'mpt-pages', 'page-change-profile' );
$pages_options[] = MPT_Options::get_option_value( 'mpt-pages', 'page-change-password' );
$my_roles        = $member_data->get_roles();
$args            = [];
$meta_key        = [];
if ( ! empty( $my_roles ) && ! is_wp_error( $my_roles ) ) {
	$my_roles = wp_list_pluck( $my_roles, 'term_id' );

	foreach ( $my_roles as $role_id ) {
		$meta_key[] = [
			'key'     => '_mpt_role',
			'value'   => (string) $role_id,
			'compare' => 'LIKE',
		];
	}

	$args = [
		'post_type'      => 'page',
		'posts_per_page' => - 1,
		'no_found_rows'  => true,
		'meta_query'     => wp_parse_args(
			$meta_key,
			[
				'relation' => 'OR',
			]
		),
	];
}

$pages_query = new \WP_Query( $args );
?>

	<h2><?php esc_html_e( 'Account', 'mpt' ); ?></h2>
	<ul>
		<?php foreach ( array_filter( $pages_options ) as $option_id ) : ?>
			<li><a href="<?php echo esc_url( get_permalink( $option_id ) ); ?>"><?php echo esc_html( get_the_title( $option_id ) ); ?></a></li>
		<?php endforeach; ?>
	</ul>
<?php if ( $pages_query->have_posts() ) : ?>
	<h2><?php esc_html_e( 'Your resources', 'mpt' ); ?></h2>
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

<?php
else:
	printf( __( 'No page associated with your role has been found.', 'mpt' ) );
endif;
