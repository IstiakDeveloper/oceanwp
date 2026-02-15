<?php
/**
 * The template for displaying Project Status Taxonomy Archives
 * Shows projects filtered by status (Ongoing or Completed)
 * Full Width - No Sidebar
 *
 * @package OceanWP WordPress theme
 */

// Force remove all sidebars for this template
add_filter( 'ocean_display_sidebar', '__return_false', 999 );
remove_action( 'ocean_after_primary', 'ocean_display_sidebar' );

get_header(); 

// Get current term (ongoing or completed)
$current_term = get_queried_object();
?>

<div id="content-wrap" class="container clr">

	<div id="primary" class="content-area clr full-width-no-sidebar">

		<?php do_action( 'ocean_before_content_inner' ); ?>

		<main id="content" class="site-content clr" role="main">

			<?php do_action( 'ocean_before_content' ); ?>

			<header class="page-header">
				<h1 class="page-title">
					<?php 
					if ( $current_term->slug === 'ongoing' ) {
						_e( 'Ongoing Projects', 'oceanwp' );
					} elseif ( $current_term->slug === 'completed' || $current_term->slug === 'previous' ) {
						_e( 'Completed Projects', 'oceanwp' );
					} else {
						echo esc_html( $current_term->name );
					}
					?>
				</h1>
				<p class="archive-description">
					<?php 
					if ( $current_term->description ) {
						echo esc_html( $current_term->description );
					} else {
						if ( $current_term->slug === 'ongoing' ) {
							_e( 'Browse our currently active development projects making a difference in communities.', 'oceanwp' );
						} elseif ( $current_term->slug === 'completed' || $current_term->slug === 'previous' ) {
							_e( 'Explore our successfully completed development projects and their impact.', 'oceanwp' );
						}
					}
					?>
				</p>
				<div class="project-count-info">
					<span class="count-badge">
						<?php 
						printf( 
							_n( '%s Project', '%s Projects', $current_term->count, 'oceanwp' ), 
							number_format_i18n( $current_term->count ) 
						); 
						?>
					</span>
				</div>
			</header>

			<?php 
			// Add navigation between status pages
			$ongoing_term = get_term_by( 'slug', 'ongoing', 'project_status' );
			$completed_term = get_term_by( 'slug', 'previous', 'project_status' );
			if ( ! $completed_term ) {
				$completed_term = get_term_by( 'slug', 'completed', 'project_status' );
			}
			
			if ( $ongoing_term && $completed_term ) :
			?>
			<nav class="taxonomy-navigation">
				<a href="<?php echo get_term_link( $ongoing_term ); ?>" 
				   class="taxonomy-nav-link <?php echo ($current_term->slug === 'ongoing') ? 'active' : ''; ?>">
					<span class="nav-icon">🚀</span>
					<span class="nav-label"><?php _e( 'Ongoing', 'oceanwp' ); ?></span>
					<span class="nav-count"><?php echo $ongoing_term->count; ?></span>
				</a>
				<a href="<?php echo get_term_link( $completed_term ); ?>" 
				   class="taxonomy-nav-link <?php echo ($current_term->slug === 'completed' || $current_term->slug === 'previous') ? 'active' : ''; ?>">
					<span class="nav-icon">✓</span>
					<span class="nav-label"><?php _e( 'Completed', 'oceanwp' ); ?></span>
					<span class="nav-count"><?php echo $completed_term->count; ?></span>
				</a>
				<a href="<?php echo get_post_type_archive_link( 'ngo_project' ); ?>" class="taxonomy-nav-link">
					<span class="nav-icon">📁</span>
					<span class="nav-label"><?php _e( 'All Projects', 'oceanwp' ); ?></span>
				</a>
			</nav>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>

				<div class="projects-grid">

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
					<p><?php _e( 'No projects found in this category.', 'oceanwp' ); ?></p>
				</div>

			<?php endif; ?>

			<?php do_action( 'ocean_after_content' ); ?>

		</main><!-- #content -->

		<?php do_action( 'ocean_after_content_inner' ); ?>

	</div><!-- #primary -->

</div><!-- #content-wrap -->

<?php get_footer(); ?>
