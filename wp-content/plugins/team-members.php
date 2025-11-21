<?php
/**
 * Plugin Name: Team Members
 * Description: A plugin to manage team members with departments and postal code search functionality.
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TEAM_MEMBERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TEAM_MEMBERS_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include the main plugin class
require_once TEAM_MEMBERS_PLUGIN_PATH . 'includes/class-team-members.php';

// Initialize the plugin
add_action('plugins_loaded', array('Team_Members', 'get_instance'));