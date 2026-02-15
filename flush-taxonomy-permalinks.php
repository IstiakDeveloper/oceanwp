<?php
/**
 * Flush Permalinks for Project Status Taxonomy
 * 
 * Run this script once after adding taxonomy-project_status.php template
 * to ensure taxonomy URLs work properly
 * 
 * Access: yourdomain.com/wp-content/themes/oceanwp/flush-taxonomy-permalinks.php
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Check if user is admin
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You do not have permission to access this page.' );
}

// Flush rewrite rules
flush_rewrite_rules( false );

?>
<!DOCTYPE html>
<html>
<head>
	<title>Permalinks Flushed</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
			background: linear-gradient(135deg, #059669 0%, #047857 100%);
			color: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 100vh;
			margin: 0;
			padding: 20px;
		}
		.container {
			background: #fff;
			color: #1f2937;
			padding: 40px;
			border-radius: 12px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			max-width: 600px;
			text-align: center;
		}
		h1 {
			color: #059669;
			margin-top: 0;
			font-size: 28px;
		}
		.success-icon {
			font-size: 64px;
			margin-bottom: 20px;
		}
		p {
			font-size: 16px;
			line-height: 1.6;
			color: #4b5563;
			margin: 15px 0;
		}
		.url-list {
			background: #f9fafb;
			padding: 20px;
			border-radius: 8px;
			margin: 25px 0;
			text-align: left;
		}
		.url-list strong {
			display: block;
			color: #059669;
			margin-bottom: 12px;
			font-size: 15px;
		}
		.url-list a {
			display: block;
			color: #047857;
			text-decoration: none;
			padding: 8px 0;
			font-weight: 500;
		}
		.url-list a:hover {
			color: #059669;
		}
		.btn {
			display: inline-block;
			background: #059669;
			color: #fff;
			padding: 12px 30px;
			border-radius: 6px;
			text-decoration: none;
			font-weight: 600;
			margin-top: 20px;
			transition: all 0.3s ease;
		}
		.btn:hover {
			background: #047857;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
		}
		.code {
			background: #1f2937;
			color: #10b981;
			padding: 2px 8px;
			border-radius: 4px;
			font-family: 'Courier New', monospace;
			font-size: 14px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="success-icon">✅</div>
		<h1>Permalinks Successfully Flushed!</h1>
		<p>The WordPress rewrite rules have been refreshed. Your project status taxonomy URLs are now ready to use.</p>
		
		<div class="url-list">
			<strong>Your New Taxonomy URLs:</strong>
			<a href="<?php echo home_url( '/project-status/ongoing/' ); ?>" target="_blank">
				<?php echo home_url( '/project-status/ongoing/' ); ?>
			</a>
			<a href="<?php echo home_url( '/project-status/completed/' ); ?>" target="_blank">
				<?php echo home_url( '/project-status/completed/' ); ?>
			</a>
			<a href="<?php echo home_url( '/projects/' ); ?>" target="_blank">
				<?php echo home_url( '/projects/' ); ?> (All Projects)
			</a>
		</div>
		
		<p><strong>Note:</strong> The <span class="code">taxonomy-project_status.php</span> template will automatically handle these URLs.</p>
		
		<p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
			<strong>Security Tip:</strong> Delete this file after running it once for security purposes.
		</p>
		
		<a href="<?php echo admin_url(); ?>" class="btn">Go to WordPress Dashboard</a>
		<a href="<?php echo home_url( '/project-status/ongoing/' ); ?>" class="btn">View Ongoing Projects</a>
	</div>
</body>
</html>
