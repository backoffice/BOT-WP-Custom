<?php
/**
 * Standalone script to create relative symlinks for nested plugins.
 * Run this via Composer to fix "Invalid Header" errors on Pantheon.
 */

$nested_subdirs = ['contrib', 'custom', 'composer', 'paid'];
// On Pantheon with nested docroot, 'web' is the folder name.
$plugin_root = realpath(__DIR__ . '/../../web/app/plugins');

if (!$plugin_root) {
    fwrite(STDERR, "Error: Could not find plugins directory at 'web/app/plugins'.\n");
    exit(1);
}

foreach ($nested_subdirs as $subdir) {
    $path = "$plugin_root/$subdir";
    if (!is_dir($path)) continue;

    // Find all folders inside the nested directory
    $folders = array_filter(glob("$path/*"), 'is_dir');

    foreach ($folders as $folder) {
        $folder_name = basename($folder);
        $link = "$plugin_root/$folder_name";

        // Prevent overwriting real folders or existing symlinks
        if (!file_exists($link)) {
            echo "Creating symlink for: $subdir/$folder_name... ";
            
            // Switch to plugin root to create a valid relative symlink
            $current_dir = getcwd();
            chdir($plugin_root);
            
            // Create the link (e.g. symlink "composer/my-plugin" to "my-plugin")
            if (symlink("$subdir/$folder_name", $folder_name)) {
                echo "Success.\n";
            } else {
                echo "Failed.\n";
            }
            
            chdir($current_dir);
        }
    }
}
