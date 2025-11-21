<?php
/**
 * Main Team Members Plugin Class
 */

if (!class_exists('Team_Members')) {

    class Team_Members
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct()
        {
            $this->init();
        }

        private function init()
        {
            // Register custom post type
            add_action('init', array($this, 'register_team_member_post_type'));
            
            // Register custom taxonomy
            add_action('init', array($this, 'register_department_taxonomy'));
            
            // Add meta boxes for team member fields
            add_action('add_meta_boxes', array($this, 'add_team_member_meta_boxes'));
            
            // Save meta box data
            add_action('save_post', array($this, 'save_team_member_meta_box_data'));
            
            // Register shortcode
            add_shortcode('teammember', array($this, 'teammember_shortcode'));
            
            // Enqueue scripts and styles
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
            
            // AJAX handler for postal code search
            add_action('wp_ajax_handle_postal_code_search', array($this, 'handle_postal_code_search'));
            add_action('wp_ajax_nopriv_handle_postal_code_search', array($this, 'handle_postal_code_search'));
            
            // Add admin menu page for documentation
            add_action('admin_menu', array($this, 'add_documentation_page'));
        }

        /**
         * Register the team member custom post type
         */
        public function register_team_member_post_type()
        {
            $labels = array(
                'name'                  => 'Team Members',
                'singular_name'         => 'Team Member',
                'menu_name'             => 'Team Members',
                'name_admin_bar'        => 'Team Member',
                'archives'              => 'Team Member Archives',
                'attributes'            => 'Team Member Attributes',
                'parent_item_colon'     => 'Parent Team Member:',
                'all_items'             => 'All Team Members',
                'add_new_item'          => 'Add New Team Member',
                'add_new'               => 'Add New',
                'new_item'              => 'New Team Member',
                'edit_item'             => 'Edit Team Member',
                'update_item'           => 'Update Team Member',
                'view_item'             => 'View Team Member',
                'view_items'            => 'View Team Members',
                'search_items'          => 'Search Team Member',
                'not_found'             => 'Not found',
                'not_found_in_trash'    => 'Not found in Trash',
                'featured_image'        => 'Featured Image',
                'set_featured_image'    => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image'    => 'Use as featured image',
                'insert_into_item'      => 'Insert into team member',
                'uploaded_to_this_item' => 'Uploaded to this team member',
                'items_list'            => 'Team members list',
                'items_list_navigation' => 'Team members list navigation',
                'filter_items_list'     => 'Filter team members list',
            );

            $args = array(
                'label'                 => 'Team Member',
                'description'           => 'Team Member Description',
                'labels'                => $labels,
                'supports'              => array('title', 'editor', 'thumbnail'),
                'taxonomies'            => array('department'),
                'hierarchical'          => false,
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 25,
                'menu_icon'             => 'dashicons-groups',
                'show_in_admin_bar'     => true,
                'show_in_nav_menus'     => true,
                'can_export'            => true,
                'has_archive'           => true,
                'exclude_from_search'   => false,
                'publicly_queryable'    => true,
                'capability_type'       => 'post',
            );

            register_post_type('team_member', $args);
        }

        /**
         * Register the department custom taxonomy
         */
        public function register_department_taxonomy()
        {
            $labels = array(
                'name'                       => 'Departments',
                'singular_name'              => 'Department',
                'menu_name'                  => 'Departments',
                'all_items'                  => 'All Departments',
                'parent_item'                => 'Parent Department',
                'parent_item_colon'          => 'Parent Department:',
                'new_item_name'              => 'New Department Name',
                'add_new_item'               => 'Add New Department',
                'edit_item'                  => 'Edit Department',
                'update_item'                => 'Update Department',
                'view_item'                  => 'View Department',
                'separate_items_with_commas' => 'Separate departments with commas',
                'add_or_remove_items'        => 'Add or remove departments',
                'choose_from_most_used'      => 'Choose from the most used',
                'popular_items'              => 'Popular Departments',
                'search_items'               => 'Search Departments',
                'not_found'                  => 'Not Found',
                'no_terms'                   => 'No departments',
                'items_list'                 => 'Departments list',
                'items_list_navigation'      => 'Departments list navigation',
            );

            $args = array(
                'labels'                     => $labels,
                'hierarchical'               => true,
                'public'                     => true,
                'show_ui'                    => true,
                'show_admin_column'          => true,
                'show_in_nav_menus'          => true,
                'show_tagcloud'              => false,
                'rewrite'                    => false,
            );

            register_taxonomy('department', array('team_member'), $args);
        }

        /**
         * Add meta boxes for team member fields
         */
        public function add_team_member_meta_boxes()
        {
            add_meta_box(
                'team-member-details',
                'Team Member Details',
                array($this, 'team_member_meta_box_callback'),
                'team_member',
                'normal',
                'high'
            );
        }

        /**
         * Callback function for team member meta box
         */
        public function team_member_meta_box_callback($post)
        {
            // Add nonce for security
            wp_nonce_field('team_member_meta_box', 'team_member_meta_box_nonce');

            // Get existing values
            $position = get_post_meta($post->ID, '_team_member_position', true);
            $organizational_position = get_post_meta($post->ID, '_team_member_organizational_position', true);
            $email = get_post_meta($post->ID, '_team_member_email', true);
            $linkedin_url = get_post_meta($post->ID, '_team_member_linkedin_url', true);
            $description = get_post_meta($post->ID, '_team_member_description', true);

            echo '<table class="form-table">';
            echo '<tr><th><label for="team_member_position">Position</label></th>';
            echo '<td><input type="text" id="team_member_position" name="team_member_position" value="' . esc_attr($position) . '" style="width: 100%;" /></td></tr>';

            echo '<tr><th><label for="team_member_organizational_position">Organizational Position</label></th>';
            echo '<td><input type="text" id="team_member_organizational_position" name="team_member_organizational_position" value="' . esc_attr($organizational_position) . '" style="width: 100%;" /></td></tr>';

            echo '<tr><th><label for="team_member_email">Email</label></th>';
            echo '<td><input type="email" id="team_member_email" name="team_member_email" value="' . esc_attr($email) . '" style="width: 100%;" /></td></tr>';

            echo '<tr><th><label for="team_member_linkedin_url">LinkedIn URL</label></th>';
            echo '<td><input type="url" id="team_member_linkedin_url" name="team_member_linkedin_url" value="' . esc_attr($linkedin_url) . '" style="width: 100%;" placeholder="https://linkedin.com/in/username" /></td></tr>';

            echo '<tr><th><label for="team_member_description">Description</label></th>';
            echo '<td><textarea id="team_member_description" name="team_member_description" style="width: 100%; height: 100px;">' . esc_textarea($description) . '</textarea></td></tr>';

            echo '</table>';
        }

        /**
         * Save meta box data
         */
        public function save_team_member_meta_box_data($post_id)
        {
            // Check if nonce is set
            if (!isset($_POST['team_member_meta_box_nonce'])) {
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce($_POST['team_member_meta_box_nonce'], 'team_member_meta_box')) {
                return;
            }

            // Check if user has permission to edit post
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            // Save the fields
            if (isset($_POST['team_member_position'])) {
                update_post_meta($post_id, '_team_member_position', sanitize_text_field($_POST['team_member_position']));
            }

            if (isset($_POST['team_member_organizational_position'])) {
                update_post_meta($post_id, '_team_member_organizational_position', sanitize_text_field($_POST['team_member_organizational_position']));
            }

            if (isset($_POST['team_member_email'])) {
                update_post_meta($post_id, '_team_member_email', sanitize_email($_POST['team_member_email']));
            }

            if (isset($_POST['team_member_linkedin_url'])) {
                update_post_meta($post_id, '_team_member_linkedin_url', esc_url_raw($_POST['team_member_linkedin_url']));
            }

            if (isset($_POST['team_member_description'])) {
                update_post_meta($post_id, '_team_member_description', sanitize_textarea_field($_POST['team_member_description']));
            }
        }

        /**
         * Enqueue frontend scripts and styles
         */
        public function enqueue_frontend_scripts()
        {
            wp_enqueue_script('jquery');
            wp_enqueue_script(
                'team-members-frontend',
                TEAM_MEMBERS_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            wp_enqueue_style(
                'team-members-frontend',
                TEAM_MEMBERS_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                '1.0.0'
            );

            // Localize script for AJAX
            wp_localize_script('team-members-frontend', 'team_members_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('team_members_nonce')
            ));
        }

        /**
         * Team member shortcode
         */
        public function teammember_shortcode($atts)
        {
            $atts = shortcode_atts(array(
                'departmentid' => '',
                'count' => 1,
            ), $atts);

            ob_start();
            ?>
            <div class="team-member-search-container">
                <div class="postal-code-input">
                    <label for="postal_code_input">Enter your postal code to locate your advisor</label>
                    <input type="text" id="postal_code_input" name="postal_code" maxlength="10" placeholder="10-digit postal code" />
                    <button type="button" id="search_advisor_btn">Search</button>
                </div>
                
                <div id="advisor_results" class="advisor-results-container"></div>
            </div>

            <script type="text/javascript">
                var departmentId = <?php echo json_encode($atts['departmentid']); ?>;
                var resultCount = <?php echo json_encode($atts['count']); ?>;
            </script>
            <?php
            return ob_get_clean();
        }

        /**
         * Handle postal code search via AJAX
         */
        public function handle_postal_code_search()
        {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'team_members_nonce')) {
                wp_die('Security check failed');
            }

            $postal_code = sanitize_text_field($_POST['postal_code']);
            $department_id = intval($_POST['department_id']);
            $count = intval($_POST['count']);

            // Validate 10-digit postal code
            if (strlen($postal_code) !== 10 || !is_numeric($postal_code)) {
                wp_send_json_error('Please enter a valid 10-digit postal code');
                return;
            }

            // Query team members
            $args = array(
                'post_type' => 'team_member',
                'post_status' => 'publish',
                'posts_per_page' => -1, // Get all members
                'orderby' => 'rand', // Random order
            );

            // Add department filter if specified
            if ($department_id) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'department',
                        'field' => 'term_id',
                        'terms' => $department_id,
                    ),
                );
            }

            $team_members = get_posts($args);

            // Limit to requested count
            $selected_members = array_slice($team_members, 0, $count);

            if (empty($selected_members)) {
                wp_send_json_error('No results found');
                return;
            }

            $results_html = '<div class="advisor-results">';

            foreach ($selected_members as $member) {
                $position = get_post_meta($member->ID, '_team_member_position', true);
                $organizational_position = get_post_meta($member->ID, '_team_member_organizational_position', true);
                $email = get_post_meta($member->ID, '_team_member_email', true);
                $linkedin_url = get_post_meta($member->ID, '_team_member_linkedin_url', true);
                $description = get_post_meta($member->ID, '_team_member_description', true);

                $results_html .= '<div class="advisor-result-box">';
                
                // Add featured image if available
                if (has_post_thumbnail($member->ID)) {
                    $results_html .= get_the_post_thumbnail($member->ID, 'medium', array('class' => 'advisor-photo'));
                } else {
                    $results_html .= '<div class="advisor-photo-placeholder">No Photo</div>';
                }

                $results_html .= '<h3 class="advisor-name">' . esc_html($member->post_title) . '</h3>';
                
                if ($position) {
                    $results_html .= '<p class="advisor-position"><strong>Position:</strong> ' . esc_html($position) . '</p>';
                }
                
                if ($organizational_position) {
                    $results_html .= '<p class="advisor-org-position"><strong>Organizational Position:</strong> ' . esc_html($organizational_position) . '</p>';
                }
                
                if ($email) {
                    $results_html .= '<p class="advisor-email"><strong>Email:</strong> <a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>';
                }
                
                if ($linkedin_url) {
                    $results_html .= '<p class="advisor-linkedin"><strong>LinkedIn:</strong> <a href="' . esc_url($linkedin_url) . '" target="_blank">View Profile</a></p>';
                }
                
                if ($description) {
                    $results_html .= '<p class="advisor-description">' . esc_html($description) . '</p>';
                }

                $results_html .= '</div>';
            }

            $results_html .= '</div>';

            wp_send_json_success($results_html);
        }
        
        /**
         * Add documentation page to admin menu
         */
        public function add_documentation_page()
        {
            add_submenu_page(
                'edit.php?post_type=team_member',
                'Team Members Plugin Documentation',
                'Documentation',
                'manage_options',
                'team-members-documentation',
                array($this, 'documentation_page_content')
            );
        }
        
        /**
         * Display documentation page content
         */
        public function documentation_page_content()
        {
            ?>
            <div class="wrap">
                <h1>Team Members Plugin Documentation</h1>
                
                <div class="card">
                    <h2>Plugin Overview</h2>
                    <p>The Team Members plugin allows you to manage team members with departments and search functionality based on postal codes.</p>
                </div>
                
                <div class="card">
                    <h2>Creating Team Members</h2>
                    <p>To create a new team member:</p>
                    <ol>
                        <li>Go to <strong>Team Members > Add New</strong> in the admin menu</li>
                        <li>Enter the team member's name in the title field</li>
                        <li>Fill in the details in the Team Member Details meta box:
                            <ul>
                                <li><strong>Position:</strong> The team member's job title</li>
                                <li><strong>Organizational Position:</strong> Their position within the organization</li>
                                <li><strong>Email:</strong> Their email address</li>
                                <li><strong>LinkedIn URL:</strong> Complete URL to their LinkedIn profile</li>
                                <li><strong>Description:</strong> Additional information about the team member</li>
                            </ul>
                        </li>
                        <li>Upload a featured image (team member's photo) using the Featured Image section</li>
                        <li>Assign the team member to one or more departments using the Departments taxonomy</li>
                        <li>Click "Publish" to save the team member</li>
                    </ol>
                </div>
                
                <div class="card">
                    <h2>Managing Departments</h2>
                    <p>Departments are a custom taxonomy that allows you to categorize team members:</p>
                    <ul>
                        <li>Go to <strong>Team Members > Departments</strong> to manage departments</li>
                        <li>You can add, edit, or delete departments as needed</li>
                        <li>When editing a team member, you can assign them to one or more departments</li>
                        <li>Team members can belong to multiple departments</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>Using the [teammember] Shortcode</h2>
                    <p>The plugin provides a shortcode to display team members with postal code search functionality:</p>
                    
                    <h3>Syntax</h3>
                    <pre>[teammember departmentid="2" count="1"]</pre>
                    
                    <h3>Parameters</h3>
                    <ul>
                        <li><strong>departmentid:</strong> (Optional) The ID of the department to filter team members from</li>
                        <li><strong>count:</strong> (Optional) Number of team members to display (default is 1)</li>
                    </ul>
                    
                    <h3>Example Usage</h3>
                    <ul>
                        <li><code>[teammember]</code> - Display search for all team members</li>
                        <li><code>[teammember count="3"]</code> - Display search for 3 random team members from all departments</li>
                        <li><code>[teammember departmentid="5" count="2"]</code> - Display search for 2 random team members from department ID 5</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>How the Postal Code Search Works</h2>
                    <p>When users enter a 10-digit postal code in the search field:</p>
                    <ol>
                        <li>The system validates that exactly 10 digits were entered</li>
                        <li>It retrieves team members from the specified department (if departmentid was provided)</li>
                        <li>It randomly selects the specified number of team members (using the count parameter)</li>
                        <li>Results are displayed in a structured format with photo, name, and details</li>
                    </ol>
                    
                    <p>Each search will return different random results if there are enough team members available.</p>
                </div>
                
                <div class="card">
                    <h2>Display Format</h2>
                    <p>Each search result displays in a 400px wide container with:</p>
                    <ul>
                        <li>Team member's photo at the top (featured image)</li>
                        <li>Name below the photo</li>
                        <li>Position</li>
                        <li>Organizational Position</li>
                        <li>Email (as a clickable link)</li>
                        <li>LinkedIn profile link</li>
                        <li>Description</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>Troubleshooting</h2>
                    <ul>
                        <li><strong>No results found:</strong> Make sure you have team members created and assigned to departments</li>
                        <li><strong>Invalid postal code:</strong> Ensure you enter exactly 10 digits</li>
                        <li><strong>Shortcode not working:</strong> Check that you're using the correct syntax</li>
                    </ul>
                </div>
            </div>
            
            <style>
                .card {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    border-radius: 4px;
                    margin-bottom: 20px;
                    padding: 20px;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                
                .card h2 {
                    margin-top: 0;
                    color: #23282d;
                }
                
                pre {
                    background: #f1f1f1;
                    padding: 10px;
                    border-radius: 4px;
                    overflow-x: auto;
                }
                
                code {
                    background: #f1f1f1;
                    padding: 2px 4px;
                    border-radius: 3px;
                    font-family: Consolas, Monaco, monospace;
                    font-size: 14px;
                }
            </style>
            <?php
        }
    }
}