<?php
/**
 * Force Update Checker for Live Server
 * Run this file to verify all project files are loaded correctly
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if files exist
$theme_dir = get_template_directory();
$files_to_check = array(
    'inc/ngo-projects.php' => 'Projects Custom Post Type',
    'archive-ngo_project.php' => 'Projects Archive Template',
    'single-ngo_project.php' => 'Single Project Template',
    'assets/css/ngo-projects.css' => 'Projects Stylesheet',
);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Project Files Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            padding: 40px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e293b;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .file-check {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            background: #f8fafc;
        }
        .file-check.exists {
            background: #d1fae5;
            border-left: 4px solid #22c55e;
        }
        .file-check.missing {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
        }
        .icon {
            font-size: 24px;
            margin-right: 15px;
        }
        .file-info {
            flex: 1;
        }
        .file-name {
            font-weight: 600;
            color: #1e293b;
        }
        .file-desc {
            font-size: 14px;
            color: #64748b;
        }
        .actions {
            margin-top: 30px;
            padding: 20px;
            background: #fef3c7;
            border-radius: 6px;
        }
        .btn {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .btn:hover {
            background: #764ba2;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-success {
            background: #22c55e;
        }
        .info-box {
            margin-top: 20px;
            padding: 15px;
            background: #e0e7ff;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 NGO Projects Files Status Check</h1>
        
        <h2>File Status:</h2>
        <?php
        $all_files_exist = true;
        foreach ($files_to_check as $file => $description) {
            $file_path = $theme_dir . '/' . $file;
            $exists = file_exists($file_path);
            if (!$exists) $all_files_exist = false;
            ?>
            <div class="file-check <?php echo $exists ? 'exists' : 'missing'; ?>">
                <div class="icon"><?php echo $exists ? '✓' : '✗'; ?></div>
                <div class="file-info">
                    <div class="file-name"><?php echo $file; ?></div>
                    <div class="file-desc"><?php echo $description; ?></div>
                </div>
            </div>
        <?php } ?>
        
        <h2>Post Type & Taxonomies:</h2>
        <?php
        $post_type_exists = post_type_exists('ngo_project');
        $status_tax_exists = taxonomy_exists('project_status');
        $category_tax_exists = taxonomy_exists('project_category');
        ?>
        
        <div class="file-check <?php echo $post_type_exists ? 'exists' : 'missing'; ?>">
            <div class="icon"><?php echo $post_type_exists ? '✓' : '✗'; ?></div>
            <div class="file-info">
                <div class="file-name">NGO Project Post Type</div>
                <div class="file-desc">Custom post type 'ngo_project'</div>
            </div>
        </div>
        
        <div class="file-check <?php echo $status_tax_exists ? 'exists' : 'missing'; ?>">
            <div class="icon"><?php echo $status_tax_exists ? '✓' : '✗'; ?></div>
            <div class="file-info">
                <div class="file-name">Project Status Taxonomy</div>
                <div class="file-desc">Taxonomy 'project_status'</div>
            </div>
        </div>
        
        <div class="file-check <?php echo $category_tax_exists ? 'exists' : 'missing'; ?>">
            <div class="icon"><?php echo $category_tax_exists ? '✓' : '✗'; ?></div>
            <div class="file-info">
                <div class="file-name">Project Category Taxonomy</div>
                <div class="file-desc">Taxonomy 'project_category'</div>
            </div>
        </div>
        
        <div class="actions">
            <h3>🛠️ Actions:</h3>
            <?php if ($all_files_exist && $post_type_exists) : ?>
                <a href="<?php echo admin_url('edit.php?post_type=ngo_project'); ?>" class="btn btn-success">View Projects Dashboard</a>
                <a href="<?php echo home_url('/projects/'); ?>" class="btn btn-success">View Projects Page</a>
                <a href="<?php echo admin_url('options-permalink.php'); ?>" class="btn">Flush Permalinks</a>
            <?php else : ?>
                <p style="color: #ef4444;"><strong>⚠️ Some files are missing!</strong></p>
                <p>Please upload all required files to your live server.</p>
            <?php endif; ?>
        </div>
        
        <div class="info-box">
            <strong>💡 If permalinks are not working:</strong><br>
            1. Go to Settings → Permalinks → Save Changes<br>
            2. Clear all caches (WordPress, Server, CDN, Browser)<br>
            3. Check file permissions (Files: 644, Folders: 755)<br>
            4. Verify functions.php includes the ngo-projects.php file
        </div>
        
        <div class="info-box" style="background: #fee2e2; margin-top: 15px;">
            <strong>🗑️ Note:</strong> You can delete this file (check-project-files.php) and flush-permalinks.php after everything is working.
        </div>
    </div>
</body>
</html>
