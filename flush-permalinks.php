<?php
/**
 * Permalink Flush Script
 * Run this file once to flush permalinks and fix 404 errors
 * After running, you can delete this file
 */

// Load WordPress
require_once( '../../../wp-load.php' );

// Flush rewrite rules
flush_rewrite_rules();

// Success message
?>
<!DOCTYPE html>
<html>
<head>
    <title>Permalinks Flushed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .success-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }
        .success-icon {
            font-size: 60px;
            color: #22c55e;
            margin-bottom: 20px;
        }
        h1 {
            color: #1e293b;
            margin: 0 0 10px 0;
        }
        p {
            color: #64748b;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        .note {
            background: #fef3c7;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h1>Permalinks Flushed Successfully!</h1>
        <p>আপনার প্রজেক্ট পেজ এখন সঠিকভাবে কাজ করবে।</p>
        <a href="<?php echo home_url('/projects/'); ?>" class="btn">View Projects</a>
        <div class="note">
            <strong>দ্রষ্টব্য:</strong> এখন আপনি এই ফাইলটি (flush-permalinks.php) ডিলিট করে দিতে পারেন।
        </div>
    </div>
</body>
</html>
