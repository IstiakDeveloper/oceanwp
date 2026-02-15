<?php
/**
 * The template for displaying NGO Projects Archive
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package OceanWP WordPress theme
 */

get_header(); ?>

<div id="content-wrap" class="container clr">

	<div id="primary" class="content-area clr">

		<?php do_action( 'ocean_before_content_inner' ); ?>

		<main id="content" class="site-content clr" role="main">

			<?php do_action( 'ocean_before_content' ); ?>

			<header class="page-header">
				<h1 class="page-title"><?php _e( 'Our Development Projects', 'oceanwp' ); ?></h1>
				<p class="archive-description"><?php _e( 'Explore our ongoing and completed development projects making a difference in communities.', 'oceanwp' ); ?></p>
			</header>

			<?php
			// Get all project statuses
			$project_statuses = get_terms( array(
				'taxonomy' => 'project_status',
				'hide_empty' => true,
			) );
			
			if ( ! empty( $project_statuses ) && ! is_wp_error( $project_statuses ) ) : ?>
				<div class="project-status-filter">
					<button class="filter-btn active" data-status="all">
						<span><?php _e( 'All Projects', 'oceanwp' ); ?></span>
					</button>
					<?php foreach ( $project_statuses as $status ) : ?>
						<button class="filter-btn" data-status="<?php echo esc_attr( $status->slug ); ?>">
							<span><?php echo esc_html( $status->name ); ?></span>
							<span class="count">(<?php echo $status->count; ?>)</span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>

				<div class="projects-grid" id="projects-container">

					<?php
					// Loop through projects
					while ( have_posts() ) : the_post();
						
						// Get project meta
						$location = get_post_meta( get_the_ID(), '_project_location', true );
						$duration = get_post_meta( get_the_ID(), '_project_duration', true );
						$budget = get_post_meta( get_the_ID(), '_project_budget', true );
						$donor = get_post_meta( get_the_ID(), '_project_donor', true );
						
						// Get project status
						$statuses = get_the_terms( get_the_ID(), 'project_status' );
						$status_classes = '';
						if ( $statuses && ! is_wp_error( $statuses ) ) {
							foreach ( $statuses as $status ) {
								$status_classes .= ' project-status-' . $status->slug;
							}
						}
						?>

						<article id="post-<?php the_ID(); ?>" <?php post_class( 'project-card' . $status_classes ); ?>>
							
							<div class="project-card-inner">
								
								<?php if ( has_post_thumbnail() ) : ?>
									<div class="project-thumbnail">
										<a href="<?php the_permalink(); ?>">
											<?php the_post_thumbnail( 'large' ); ?>
											
											<?php if ( $statuses && ! is_wp_error( $statuses ) ) : ?>
												<div class="project-status-badge">
													<?php foreach ( $statuses as $status ) : ?>
														<span class="status-badge status-<?php echo esc_attr( $status->slug ); ?>">
															<?php echo esc_html( $status->name ); ?>
														</span>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>
										</a>
									</div>
								<?php endif; ?>

								<div class="project-content">
									
									<h3 class="project-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>

									<div class="project-meta-info">
										<?php if ( $location ) : ?>
											<div class="project-meta-item">
												<i class="fa fa-map-marker"></i>
												<span><?php echo esc_html( $location ); ?></span>
											</div>
										<?php endif; ?>

										<?php if ( $duration ) : ?>
											<div class="project-meta-item">
												<i class="fa fa-calendar"></i>
												<span><?php echo esc_html( $duration ); ?></span>
											</div>
										<?php endif; ?>

										<?php if ( $budget ) : ?>
											<div class="project-meta-item">
												<i class="fa fa-money"></i>
												<span><?php echo esc_html( $budget ); ?></span>
											</div>
										<?php endif; ?>
										
										<?php if ( $donor ) : ?>
											<div class="project-meta-item">
												<i class="fa fa-handshake-o"></i>
												<span><?php echo esc_html( $donor ); ?></span>
											</div>
										<?php endif; ?>
									</div>

									<?php if ( has_excerpt() ) : ?>
										<div class="project-excerpt">
											<?php the_excerpt(); ?>
										</div>
									<?php endif; ?>

									<div class="project-footer">
										<a href="<?php the_permalink(); ?>" class="project-read-more">
											<span><?php _e( 'Read More', 'oceanwp' ); ?></span>
											<i class="fa fa-arrow-right"></i>
										</a>
									</div>

								</div><!-- .project-content -->

							</div><!-- .project-card-inner -->

						</article><!-- .project-card -->

					<?php endwhile; ?>

				</div><!-- .projects-grid -->

				<?php
				// Pagination
				oceanwp_pagination();
				?>

			<?php else : ?>

				<div class="no-projects-found">
					<p><?php _e( 'No projects found.', 'oceanwp' ); ?></p>
				</div>

			<?php endif; ?>

			<?php do_action( 'ocean_after_content' ); ?>

		</main><!-- #content -->

		<?php do_action( 'ocean_after_content_inner' ); ?>

	</div><!-- #primary -->

</div><!-- #content-wrap -->

<script>
jQuery(document).ready(function($) {
	// Project filtering
	$('.filter-btn').on('click', function() {
		var status = $(this).data('status');
		
		// Update active button
		$('.filter-btn').removeClass('active');
		$(this).addClass('active');
		
		// Filter projects
		if (status === 'all') {
			$('.project-card').fadeIn(300);
		} else {
			$('.project-card').hide();
			$('.project-status-' + status).fadeIn(300);
		}
	});
});
</script>

<?php get_footer(); ?>
