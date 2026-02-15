<?php
/**
 * The template for displaying single NGO Project
 *
 * @package OceanWP WordPress theme
 */

get_header(); ?>

<?php do_action( 'ocean_before_content_wrap' ); ?>

<div id="content-wrap" class="container clr">

	<?php do_action( 'ocean_before_primary' ); ?>

	<div id="primary" class="content-area clr">

		<?php do_action( 'ocean_before_content' ); ?>

		<div id="content" class="site-content clr">

			<?php do_action( 'ocean_before_content_inner' ); ?>

			<?php
			// Start loop
			while ( have_posts() ) : the_post();

				// Get project meta
				$location = get_post_meta( get_the_ID(), '_project_location', true );
				$duration = get_post_meta( get_the_ID(), '_project_duration', true );
				$budget = get_post_meta( get_the_ID(), '_project_budget', true );
				$donor = get_post_meta( get_the_ID(), '_project_donor', true );
				$goals = get_post_meta( get_the_ID(), '_project_goals', true );
				$beneficiaries = get_post_meta( get_the_ID(), '_project_beneficiaries', true );
				$outcomes = get_post_meta( get_the_ID(), '_project_outcomes', true );
				
				// Get project status
				$statuses = get_the_terms( get_the_ID(), 'project_status' );
				$categories = get_the_terms( get_the_ID(), 'project_category' );
				?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-project' ); ?>>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="project-hero-image">
							<?php the_post_thumbnail( 'full' ); ?>
						</div>
					<?php endif; ?>

					<div class="project-header-section">
						<div class="project-header-content">
							
							<?php if ( $statuses && ! is_wp_error( $statuses ) ) : ?>
								<div class="project-status-badges">
									<?php foreach ( $statuses as $status ) : ?>
										<span class="status-badge status-<?php echo esc_attr( $status->slug ); ?>">
											<?php echo esc_html( $status->name ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<h1 class="project-main-title"><?php the_title(); ?></h1>
							
							<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
								<div class="project-categories">
									<?php foreach ( $categories as $category ) : ?>
										<span class="project-category"><?php echo esc_html( $category->name ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

						</div>

						<div class="project-key-info">
							<h3 class="info-title"><?php _e( 'The Project at a Glance', 'oceanwp' ); ?></h3>
							
							<?php if ( $location ) : ?>
								<div class="info-box">
									<div class="info-label"><?php _e( 'Location', 'oceanwp' ); ?></div>
									<div class="info-value"><?php echo esc_html( $location ); ?></div>
								</div>
							<?php endif; ?>
							
							<?php if ( $duration ) : ?>
								<div class="info-box">
									<div class="info-label"><?php _e( 'Duration', 'oceanwp' ); ?></div>
									<div class="info-value"><?php echo esc_html( $duration ); ?></div>
								</div>
							<?php endif; ?>
							
							<?php if ( $budget ) : ?>
								<div class="info-box">
									<div class="info-label"><?php _e( 'Budget', 'oceanwp' ); ?></div>
									<div class="info-value"><?php echo esc_html( $budget ); ?></div>
								</div>
							<?php endif; ?>
							
							<?php if ( $donor ) : ?>
								<div class="info-box">
									<div class="info-label"><?php _e( 'Funded By', 'oceanwp' ); ?></div>
									<div class="info-value"><?php echo esc_html( $donor ); ?></div>
								</div>
							<?php endif; ?>
							
							<?php if ( $beneficiaries ) : ?>
								<div class="info-box">
									<div class="info-label"><?php _e( 'Beneficiaries', 'oceanwp' ); ?></div>
									<div class="info-value"><?php echo esc_html( $beneficiaries ); ?></div>
								</div>
							<?php endif; ?>
						</div>
					</div><!-- .project-header-section -->

					<div class="project-content-section">

						<?php if ( has_excerpt() ) : ?>
							<div class="project-summary">
								<h2><?php _e( 'Project Overview', 'oceanwp' ); ?></h2>
								<div class="summary-content">
									<?php echo wpautop( get_the_excerpt() ); ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( get_the_content() ) : ?>
							<div class="project-description">
								<h2><?php _e( 'About the Project', 'oceanwp' ); ?></h2>
								<div class="description-content">
									<?php the_content(); ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $goals ) : ?>
							<div class="project-goals">
								<h2><?php _e( 'Project Goals', 'oceanwp' ); ?></h2>
								<div class="goals-content">
									<?php echo wpautop( esc_html( $goals ) ); ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $outcomes ) : ?>
							<div class="project-outcomes">
								<h2><?php _e( 'Key Outcomes & Activities', 'oceanwp' ); ?></h2>
								<div class="outcomes-list">
									<?php
									$outcomes_array = explode( "\n", $outcomes );
									if ( ! empty( $outcomes_array ) ) :
									?>
										<ul>
											<?php foreach ( $outcomes_array as $outcome ) : 
												$outcome = trim( $outcome );
												if ( ! empty( $outcome ) ) :
											?>
												<li><?php echo esc_html( $outcome ); ?></li>
											<?php 
												endif;
											endforeach; 
											?>
										</ul>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>

					</div><!-- .project-content-section -->

					<div class="project-navigation">
						<div class="nav-links">
							<?php
							// Previous project
							$prev_post = get_previous_post();
							if ( $prev_post ) :
							?>
								<div class="nav-previous">
									<a href="<?php echo get_permalink( $prev_post->ID ); ?>">
										<span class="nav-label"><i class="fa fa-arrow-left"></i> <?php _e( 'Previous Project', 'oceanwp' ); ?></span>
										<span class="nav-title"><?php echo esc_html( $prev_post->post_title ); ?></span>
									</a>
								</div>
							<?php endif; ?>

							<div class="nav-back-to-archive">
								<a href="<?php echo get_post_type_archive_link( 'ngo_project' ); ?>">
									<i class="fa fa-th"></i>
									<span><?php _e( 'All Projects', 'oceanwp' ); ?></span>
								</a>
							</div>

							<?php
							// Next project
							$next_post = get_next_post();
							if ( $next_post ) :
							?>
								<div class="nav-next">
									<a href="<?php echo get_permalink( $next_post->ID ); ?>">
										<span class="nav-label"><?php _e( 'Next Project', 'oceanwp' ); ?> <i class="fa fa-arrow-right"></i></span>
										<span class="nav-title"><?php echo esc_html( $next_post->post_title ); ?></span>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div><!-- .project-navigation -->

				</article><!-- .single-project -->

			<?php endwhile; ?>

			<?php do_action( 'ocean_after_content_inner' ); ?>

		</div><!-- #content -->

		<?php do_action( 'ocean_after_content' ); ?>

	</div><!-- #primary -->

	<?php do_action( 'ocean_after_primary' ); ?>

</div><!-- #content-wrap -->

<?php do_action( 'ocean_after_content_wrap' ); ?>

<?php get_footer(); ?>
