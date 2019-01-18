<?php

/**
 * Admin Menu
 */
class Squares_Team_Admin_Menu {

    /**
     * Kick-in the class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     * Add menu items
     *
     * @return void
     */
    public function admin_menu() {

        add_submenu_page( 'dls-squares-admin', __( 'Teams', '' ), __( 'Teams', '' ), 'manage_options', 'dls-squares-admin-teams', array( $this, 'plugin_page' ) );

    }

    /**
     * Handles the plugin page
     *
     * @return void
     */
    public function plugin_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ($action) {
            case 'view':

                $template = dirname( __FILE__ ) . '/views/teams-single.php';
                break;

            case 'edit':
                $template = dirname( __FILE__ ) . '/views/teams-edit.php';
                break;

            case 'new':
                $template = dirname( __FILE__ ) . '/views/teams-new.php';
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/teams-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }
}