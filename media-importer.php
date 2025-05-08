<?php
/*
Plugin Name: Media Importer (Register Only Original Used Images)
Description: Scans wp-content/uploads and registers only original images (used in pages) into the Media Library.
Version: 1.1
Author: OpenAI ChatGPT
*/

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_management_page('Media Importer', 'Media Importer', 'manage_options', 'media-importer', 'media_importer_page');
});

function media_importer_page() {
    if (!current_user_can('manage_options')) return;
    echo '<div class="wrap"><h1>Media Importer</h1>';

    if (isset($_POST['media_importer_run'])) {
        media_importer_run();
    } else {
        echo '<form method="post"><p>This will scan your uploads folder and register original images used in pages/posts.</p>';
        echo '<p><button type="submit" name="media_importer_run" class="button button-primary">Run Import</button></p></form>';
    }

    echo '</div>';
}

function media_importer_run() {
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'];
    $base_url = $upload_dir['baseurl'];

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));

    $imported = 0;
    $skipped = 0;

    global $wpdb;

    // Get all content from posts/pages
    $post_contents = $wpdb->get_col("
        SELECT post_content FROM $wpdb->posts 
        WHERE post_type IN ('post', 'page') AND post_status IN ('publish', 'draft')
    ");
    $all_content = implode(' ', $post_contents);

    foreach ($rii as $file) {
        if ($file->isDir()) continue;

        $filepath = $file->getPathname();
        $rel_path = str_replace($base_dir, '', $filepath);
        $url = $base_url . $rel_path;

        if (!preg_match('/\.(jpg|jpeg|png|gif)$/i', $filepath)) continue;

        // Skip resized versions like image-300x200.jpg
        if (preg_match('/-\d+x\d+\.(jpg|jpeg|png|gif)$/i', $filepath)) {
            $skipped++;
            continue;
        }

        // Only import if the image is actually used in post/page content
        if (strpos($all_content, $rel_path) === false && strpos($all_content, basename($filepath)) === false) {
            $skipped++;
            continue;
        }

        // Skip if already registered by GUID
        $already = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE guid = %s",
            $url
        ));
        if ($already) {
            $skipped++;
            continue;
        }

        $filetype = wp_check_filetype($filepath, null);
        $attachment = [
            'guid' => $url,
            'post_mime_type' => $filetype['type'],
            'post_title' => basename($filepath),
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $attach_id = wp_insert_attachment($attachment, $filepath);
        $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $attach_data);

        $imported++;
    }

    echo '<p><strong>✅ Import complete:</strong></p>';
    echo '<ul><li>Imported: ' . $imported . '</li><li>Skipped: ' . $skipped . '</li></ul>';
    echo '<p><a href="' . admin_url('upload.php') . '" class="button">View Media Library</a></p>';
}
