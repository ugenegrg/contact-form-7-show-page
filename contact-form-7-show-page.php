<?php
/**
 * Plugin Name: Contact Form 7 - Show Page
 * Description: An add-on for Contact Form 7 that shows the all the post types where the contact form is being used.
 * Version: 1.0.3
 * Author: Sachyya, Ugene
 * Author URI:
 * License: GPLv3
 */

/**
 * Show admin notice on CF7 dependency.
 * @return string Notice about the plugin dependency.
 */
function wpcf7sp_admin_notice() {
    // Verify that CF7 is active and updated to the required version (currently 3.9.0)
    if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
        echo __( '<div class="error"><p><strong>Contact Form 7</strong> is not activated. The Contact Form 7 plugin must be installed and activated before you can use <strong>Contact Form 7 - Show Page</strong> plugin.</p></div>', 'wpcf7sp' );
    }
}

// add_action( 'admin_notices', 'wpcf7sp_admin_notice' );

require_once untrailingslashit( dirname( __FILE__ ) ) . '/tgmpa/call.php';

/**
 * Load style file
 */
function wpcf7sp_load_style() {
    wp_enqueue_style( 'wpcf7sp_style', plugins_url( '/css/show-page.css', __FILE__ ), '', time(), 'all' );
}

add_action( 'admin_footer', 'wpcf7sp_load_style' );


/**
 * Add extra panel to form editor.
 *
 * @param  Array $panels Array form editro panel args.
 *
 * @return Array $panels  Array form editro panel args.
 */
function wpcf7sp_add_panel( $panels ) {
    $post = wpcf7_get_current_contact_form();
    if ( current_user_can( 'wpcf7_edit_contact_form', $post->id() ) ) {
        $panels['cf7-show-page-panel'] = array(
            'title'    => __( 'CF7 Show Pages', 'wpcf7sp' ),
            'callback' => 'wpcf7sp_editor_panel_cf7_show_page'
        );
    }

    return $panels;
}

add_filter( 'wpcf7_editor_panels', 'wpcf7sp_add_panel' );


// Get search string 
function wpcf7sp_search_string( $form_id ) {
    $wpcf7sp_form_id       = $form_id->id();
    
    $wpcf7sp_search_string = 'contact-form-7 id="' . $wpcf7sp_form_id . '"';
    return $wpcf7sp_search_string;
}

/**

Search on postypes.

*/
function wpcf7sp_search_on_posttypes( $form_id ) {
    $wpcf7sp_search_string = wpcf7sp_search_string( $form_id );
    $wpcf7sp_posttypes     = apply_filters( 'wpcf7sp_support_posttypes', get_post_types( array( 'public' => true ) ) );

    if ( ! ( is_array( $wpcf7sp_posttypes ) || is_string( $wpcf7sp_posttypes ) ) ) {
        $wpcf7sp_posttypes = get_post_types( array( 'public' => true ) );
    }

    $post_query = new WP_Query( array(
        's'         => $wpcf7sp_search_string,
        'post_type' => $wpcf7sp_posttypes,
        'posts_per_page' => -1
    ) );

    if ( $post_query->have_posts() ) { ?>
        <h4><?php _e( 'Lists of post types using this form.', 'wpcf7sp' ); ?></h4>
        <table class="wpcf7sp">
            <tbody>
            <tr>
                <th width="55%"><?php _e( 'Title', 'wpcf7sp' ); ?></th>
                <th width="25%"><?php _e( 'Post Type', 'wpcf7sp' ); ?></th>
                <th width="20%"></th>
            </tr>
            <?php
            while ( $post_query->have_posts() ) {
                $post_query->the_post();
                $post_type = get_post_type();

                ?>
                <tr>
                    <td width="50%"><?php the_title(); ?></td>
                    <td width="25%"><?php echo esc_html( $post_type ); ?></td>
                    <td width="25%" class="wpcf7sp-opt">
                        <a href="<?php the_permalink(); ?>" class="wpcf7sp-alink" target="_blank">View</a>
                        <a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>" class="wpcf7sp-alink"
                           target="_blank"><?php _e( 'Edit', 'wpcf7sp' ); ?></a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    } else { ?>
        <h4><?php _e( 'No "Posts" or "Pages" use this form right now.', 'wpcf7sp' ); ?></h4>
    <?php } 
}

/**

Search on text widget.

*/
function wpcf7sp_search_on_text_widget( $form_id ) {
    $wpcf7sp_search_string = wpcf7sp_search_string( $form_id );
    // Query for the text widgets with the contact form.
    global $wpdb;
    // Get prefixed table name.
    $wpcf7sp_table = $wpdb->prefix . 'options';
    // Prepare query which searches for option name with widget_text and also verify for the empty and no form used condition.
    $wpcf7sp_widget_query   = $wpdb->prepare( "SELECT * FROM `$wpcf7sp_table` WHERE `option_name` LIKE '%%widget_text%%' AND `option_value` LIKE '%%%s%%'", $wpcf7sp_search_string );
    $wpcf7sp_widget_results = $wpdb->get_results( $wpcf7sp_widget_query );
    if ( isset( $wpcf7sp_widget_results[0] )) {
        $wpcf7sp_widget_results = $wpcf7sp_widget_results[0];
        // Unserialize data which contains all the text widgets found.
        $wpcf7sp_text_widgets = maybe_unserialize( $wpcf7sp_widget_results->option_value );

        // Prepare search string to be searched in result.
        $serach_expression = '/\[' . $wpcf7sp_search_string . '/';
        $widget_ids        = [];
        foreach ( $wpcf7sp_text_widgets as $key => $value ) {
            if ( is_array( $value ) ) {
                // Check if the value is set and not empty and match with the search expression.
                if ( isset( $value['text'] ) && ! empty( $value['text'] ) && preg_match( $serach_expression, $value['text'] ) ) {
                    // Append 'text-' to make it comparable in later in_array condition.
                    $widget_ids[] = 'text-' . $key;
                }
            }
        }
        // Query for the sidebars with the above found text widgets.
        $widget_resp_sidebar_query = "SELECT * FROM `$wpcf7sp_table` WHERE `option_name` = 'sidebars_widgets'";
        $widget_resp_sidebar_res   = $wpdb->get_results( $widget_resp_sidebar_query );
        // Unserialize data which contains all the sidebars.
        $widget_resp_sidebar_res = maybe_unserialize( $widget_resp_sidebar_res[0]->option_value );

        $side_bar_ids = [];
        foreach ( $widget_resp_sidebar_res as $key => $value ) {
            if ( ! empty( $value ) && is_array( $value ) ) {
                for ( $i = 0; $i < count( $value ); $i ++ ) {
                    // Check if the above widget_ids is in the recently found array.
                    if ( in_array( $value[ $i ], $widget_ids ) ) {
                        $side_bar_ids[] = $key;
                    }
                }
            }
        }
        // Removing any repeating sidebar id.
        $side_bar_ids = array_unique( $side_bar_ids );
        ?>

        <h4><?php _e( 'Lists of widgets using this form.', 'wpcf7sp' ); ?></h4>
        <?php if ( ! is_null( $wpcf7sp_text_widgets ) ): ?>
            <table class="wpcf7sp" style="width: 50%;">
                <tbody>
                <tr>
                    <th><?php _e( 'Widget: Text', 'wpcf7sp' ); ?></th>
                </tr>
                <?php
                foreach ( $side_bar_ids as $value ) {
                    global $wp_registered_sidebars;
                    if ( isset( $wp_registered_sidebars[ $value ] ) ) {
                        echo '<tr><td>' . esc_html( $wp_registered_sidebars[ $value ]['name'] ) . '</td></tr>';
                    }
                }
                ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php } else { ?>
        <h4><?php _e( 'No "Text widgets" uses this form right now.', 'wpcf7sp' ); ?></h4>
    <?php }
}

/**

Search on text widget.

*/
function wpcf7sp_search_on_custom_html_widget( $form_id ) {
    $wpcf7sp_search_string = wpcf7sp_search_string( $form_id );
    // Query for the text widgets with the contact form.
    global $wpdb;
    // Get prefixed table name.
    $wpcf7sp_table = $wpdb->prefix . 'options';
    // Prepare query which searches for option name with widget_text and also verify for the empty and no form used condition.
    $wpcf7sp_widget_query   = $wpdb->prepare( "SELECT * FROM `$wpcf7sp_table` WHERE `option_name` LIKE '%%widget_custom_html%%' AND `option_value` LIKE '%%%s%%'", $wpcf7sp_search_string );
    $wpcf7sp_widget_results = $wpdb->get_results( $wpcf7sp_widget_query );
    if ( isset( $wpcf7sp_widget_results[0] )) {
        $wpcf7sp_widget_results = $wpcf7sp_widget_results[0];
        // Unserialize data which contains all the text widgets found.
        $wpcf7sp_text_widgets = maybe_unserialize( $wpcf7sp_widget_results->option_value );

        // Prepare search string to be searched in result.
        $serach_expression = '/\[' . $wpcf7sp_search_string . '/';
        $widget_ids        = [];
        foreach ( $wpcf7sp_text_widgets as $key => $value ) {
            if ( is_array( $value ) ) {
                // Check if the value is set and not empty and match with the search expression.
                if ( isset( $value['content'] ) && ! empty( $value['content'] ) && preg_match( $serach_expression, $value['content'] ) ) {
                    // Append 'text-' to make it comparable in later in_array condition.
                    $widget_ids[] = 'custom_html-' . $key;
                }
            }
        }
        // Query for the sidebars with the above found text widgets.
        $widget_resp_sidebar_query = "SELECT * FROM `$wpcf7sp_table` WHERE `option_name` = 'sidebars_widgets'";
        $widget_resp_sidebar_res   = $wpdb->get_results( $widget_resp_sidebar_query );
        // Unserialize data which contains all the sidebars.
        $widget_resp_sidebar_res = maybe_unserialize( $widget_resp_sidebar_res[0]->option_value );

        $side_bar_ids = [];
        foreach ( $widget_resp_sidebar_res as $key => $value ) {
            if ( ! empty( $value ) && is_array( $value ) ) {
                for ( $i = 0; $i < count( $value ); $i ++ ) {
                    // Check if the above widget_ids is in the recently found array.
                    if ( in_array( $value[ $i ], $widget_ids ) ) {
                        $side_bar_ids[] = $key;
                    }
                }
            }
        }
        // Removing any repeating sidebar id.
        $side_bar_ids = array_unique( $side_bar_ids );
        ?>

        <?php if ( ! is_null( $wpcf7sp_text_widgets ) ): ?>
            <table class="wpcf7sp" style="width: 50%;">
                <tbody>
                <tr>
                    <th><?php _e( 'Widget: Custom HTML', 'wpcf7sp' ); ?></th>
                </tr>
                <?php
                foreach ( $side_bar_ids as $value ) {
                    global $wp_registered_sidebars;
                    if ( isset( $wp_registered_sidebars[ $value ] ) ) {
                        echo '<tr><td>' . esc_html( $wp_registered_sidebars[ $value ]['name'] ) . '</td></tr>';
                    }
                }
                ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php } else { ?>
        <h4><?php _e( 'No "Custom HTML" widgets uses this form right now.', 'wpcf7sp' ); ?></h4>
    <?php }
}

/**
 * Show the panel itself
 *
 */
function wpcf7sp_editor_panel_cf7_show_page( $form_id ) {

    ?>
    <h2><?php echo esc_html( __( 'Contact Form 7 - Show Page', 'wpcf7sp' ) ); ?></h2>

    <?php 

    wpcf7sp_search_on_posttypes( $form_id ); 

    wpcf7sp_search_on_text_widget( $form_id );

    wpcf7sp_search_on_custom_html_widget( $form_id );
    
}