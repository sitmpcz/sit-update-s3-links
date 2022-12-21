<?php
/**
 * Plugin Name: SIT update links
 * Description: Update file links in editor
 * Version: 1.0.1
 * Author: SIT:Jaroslav Dvořák
 **/

if ( !defined('SCN_PLUGIN_PATH') ) {
    define( 'SCN_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
}

// Odkaz na stranku naseho nastaveni v admin
// Submenu Hlavniho nastaveni
add_action( 'admin_menu', 'sul_add_admin_plugin_menu' );

function sul_add_admin_plugin_menu():void {

    add_submenu_page(
        'options-general.php',
        'SIT Update S3 Links',
        'SIT Update S3 Links',
        'administrator',
        'sit-update-links-settings',
        'sul_add_admin_plugin_page'
    );

    //call register settings function
    add_action( 'admin_init', 'sul_register_plugin_settings' );
}

function sul_register_plugin_settings():void {

    register_setting( "situl_options", "situl_post_types" );
    register_setting( "situl_options", "situl_domain" );
}

function sul_add_admin_plugin_page():void {

    if ( isset( $_GET['_wpnonce'], $_GET['action'] ) ) {

        $action = sanitize_key( $_GET['action'] );
        $nonce = sanitize_key( $_GET['_wpnonce'] );

        // Nonce verification.
        if ( $action === "sul" && wp_verify_nonce( $nonce, $action ) ) {
            $html = sul_do_it();
            require_once __DIR__ . "/views/ok.php";
        }
    }
    else {
        require_once __DIR__ . "/views/admin-option-page.php";
    }
}

function sul_do_it():string {

    global $wpdb;

    $html = "";

    // Selected CPTs
    $select_post_types = get_option( "situl_post_types" );

    // Pokud pouzijeme tento plugin, na 99% by tam WPMF_AWS3_SETTINGS mela byt definovana
    if ( sul_check_is_s3() === true ) {
        // Settings
        $settings = sul_get_s3_settings();

        if ( !$settings ) {
            return "Není co řešit";
        }

        $cpts = "'" . implode("', '", $select_post_types ) . "'";

        $results = $wpdb->get_results( "SELECT ID, post_title, post_content FROM {$wpdb->prefix}posts WHERE post_type IN ({$cpts})", OBJECT );

        if ( $results ) {

            $table_name = $wpdb->prefix . "posts";

            foreach ( $results as $result ) {

                $post_content = $result->post_content;
                $new_content = str_replace( $settings["old_url"], $settings["new_url"], $post_content );

                $update = $wpdb->update( $table_name, [ "post_content" => $new_content ], ["ID" => $result->ID ] );

                $title = $result->post_title . " (ID: ". $result->ID . ")<br>";

                if ( $update ) {
                    $html .= "Aktualizováno: " . $title;
                }
                else {
                    $html .= "Není co aktualizovat: " . $title;
                }
            }
        }
    }

    return $html;
}

function sul_get_s3_settings():array {

    $settings = [];

    if ( sul_check_is_s3() === true ) {
        // S3
        $wpmf_settings = unserialize( WPMF_AWS3_SETTINGS );

        $bucket = $wpmf_settings["bucket"];
        $region = $wpmf_settings["region"];
        $root_folder_name = $wpmf_settings["root_folder_name"];

        // Adresy
        $domain = get_option( "situl_domain" );
        $settings["new_url"] = "https://s3.". $region .".amazonaws.com/". $bucket ."/". $root_folder_name ."/var/www/rocketstack/web/app/uploads/";
        $settings["old_url"] = $domain . "/app/uploads/";

        $settings["bucket"] = $bucket;
        $settings["region"] = $region;
        $settings["root_folder_name"] = $root_folder_name;
    }

    return $settings;
}

function sul_check_is_s3():bool {
    return ( defined( 'WPMF_AWS3_SETTINGS' ) );
}

function sul_get_run_url():string {
    return wp_nonce_url( "?page=sit-update-links-settings&action=sul", "sul" );
}
