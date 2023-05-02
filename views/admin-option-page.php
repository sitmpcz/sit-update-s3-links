<?php
// Vybrane CPT
$select_post_types = get_option( "situl_post_types" );
// Zadana domena
$domain = get_option( "situl_domain" );
// Stara cesta
$old_path = get_option( "situl_old_path" );
// Vsechny CPT
$post_types = get_post_types( array (
    'show_ui' => true,
    'show_in_menu' => true,
), 'objects' );

$settings = sul_get_s3_settings();
?>
<div class="wrap">
    <h1>Update S3 links</h1>
    <p>Update S3 link inside editor content</p>
    <form method="post" action="options.php">
        <?php
        settings_fields("situl_options");
        do_settings_sections("situl_options");
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">URLs</th>
                <td>
                    <?php
                    if ( $settings ) {
                        echo '<p><b>Old:</b> '. $settings["old_url"] .'</p>';
                        echo '<p><b>New:</b> '. $settings["new_url"] .'</p>';
                    }
                    ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Post Types</th>
                <td>
                    <?php
                    foreach ( $post_types  as $post_type ) {
                        if ( $post_type->name == 'attachment' ) continue;
                        ?>
                        <input type="checkbox" name="situl_post_types[]" value="<?php echo $post_type->name; ?>" id="<?php echo $post_type->name; ?>" <?php if ( isset( $select_post_types ) && is_array( $select_post_types ) ) { if ( in_array( $post_type->name, $select_post_types ) ) { echo 'checked="checked"'; } } ?>>
                        <label for="<?php echo $post_type->name; ?>">
                            <?php echo $post_type->label; ?>
                        </label><br>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Doména</th>
                <td>
                    <input type="url" name="situl_domain" value="<?php echo $domain; ?>" id="situl_domain" class="regular-text code" />
                    <p>Doména obsažená ve starých URL adresách</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Stará cesta</th>
                <td>
                    <input type="text" name="situl_old_path" value="<?php echo $old_path; ?>" id="situl_old_path" class="regular-text code" />
                    <p>Hledaná stará cesta (/app/uploads/ nebo /wp-content/uploads/)</p>
                </td>
            </tr>
            <?php
            if ( sul_check_is_s3() === true ) :
                ?>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>
                        <a href="<?php echo sul_get_run_url(); ?>" class="button button-secondary">Spustit update</a>
                        <p>Jestli si jseš jistej, tak to zmáčkni :D</p>
                    </td>
                </tr>
            <?php
            endif;
            ?>
        </table>
        <?php
        submit_button();
        ?>
    </form>
</div>
