<?php

/**
 * Admin Menu
 */
class Squares_Game_Admin_Menu {

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

        add_submenu_page( 'dls-squares-admin', __( 'Games', '' ), __( 'Games', '' ), 'manage_options', 'dls-squares-admin-games', array( $this, 'plugin_page' ) );
        
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

                $template = dirname( __FILE__ ) . '/views/games-single.php';
                break;

            case 'edit':
                $template = dirname( __FILE__ ) . '/views/games-edit.php';
                break;

            case 'new':
                $template = dirname( __FILE__ ) . '/views/games-new.php';
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/games-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }
}