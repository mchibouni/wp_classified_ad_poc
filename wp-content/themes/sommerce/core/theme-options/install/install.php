<?php                             
require_once 'install_panel.php';  

function yiw_install_add_styles() 
{
    if ( ! ( isset( $_GET['page'] ) AND ( $_GET['page'] == basename(__FILE__) OR preg_match('/sub-page-(.*)/', $_GET['page']) ) ) )
        return;
    
    $file_dir = get_template_directory_uri()."/admin-options/include";
    
    wp_enqueue_style("functions", $file_dir."/functions.css", false, "1.0", "all"); 
    wp_enqueue_style("wp-admin");
}
add_action( 'admin_init', 'yiw_install_add_styles' );
        
function yiw_install_add_scripts() {    
    if ( ! ( isset( $_GET['page'] ) AND ( $_GET['page'] == basename(__FILE__) OR preg_match('/sub-page-(.*)/', $_GET['page']) ) ) )
        return;
    
    $file_dir = get_template_directory_uri()."/admin-options/include";
    
    $deps = array(
        'jquery'
    );
                                                                                                                                                 
    wp_enqueue_script("rm_script", $file_dir."/rm_script.js", $deps, '1.0', true); 
}                   
add_action('wp_enqueue_scripts', 'yiw_install_add_scripts');   
    
// tables to export or import
$yiw_wptables = array( 'posts', 'postmeta', 'terms', 'term_taxonomy', 'term_relationships' );

function yiw_export_theme()
{
    global $wpdb, $yiw_wptables;
    
    $db = array();  // all backup will be in this array
    
    set_time_limit(0);
    
    // retrive all values of tables
    foreach( $yiw_wptables as $table )
    {
        if( $table == 'posts' )
            $where = " WHERE post_type <> 'revision'";
        else
            $where = '';
            
        $db[$table] = $wpdb->get_results( "SELECT * FROM {$wpdb->$table}{$where}", ARRAY_A );
    }
    
    // options
    $theme = get_option( 'stylesheet' );
    $sql = "SELECT blog_id, option_name, option_value, autoload FROM {$wpdb->options} 
WHERE   option_name = '" . YIW_OPTIONS_DB . "_" . get_template() . "' 
OR      option_name = 'sidebars_widgets'
OR      option_name = 'show_on_front'
OR      option_name = 'page_on_front'
OR      option_name = 'page_for_posts'
OR      option_name LIKE 'widget%'
OR      option_name LIKE 'theme\_mods\_%'";
    
    $sql .= ';';

    $db['options'] = $wpdb->get_results( $sql, ARRAY_A );
    
    array_walk_recursive( $db, 'yiw_convert_url', 'in_export' );
    //yiw_debug( $db );
    
    $info['content'] = gzcompress( base64_encode( serialize( $db ) ), 9 );
    $info['filename'] = get_option('template') . '_export_' . time() . '.gz';

    return $info;
}   

function yiw_import_theme()
{
    global $wpdb, $yiw_wptables;               
    
    $wpdb->show_errors();
    
    if( !isset( $_FILES['import-file'] ) )
        wp_die( __( "The file you have insert doesn't valid.", 'yiw' ) );    
    
    set_time_limit(0);
    
    switch ( substr( $_FILES['import-file']['name'], -3 ) ) {
    
       case 'xml' :
           $error = __( sprintf( "The file you have insert is a WordPress eXtended RSS (WXR) file. You need to use this into the %s admin page to import this file. Here only <b>.gz</b> file are allowed.", '<a href="'.admin_url( 'import.php', false ).'">'.__( 'Tools -> Import', 'yiw' ).'</a>' ), 'yiw' ); 
           yiw_error_message($error);
           return;
    
       case 'zip' :
       case 'rar' :
           $error = __( sprintf( "The file you have insert is a ZIP or RAR file, that it doesn't allowed in this case. Here only <b>.gz</b> file are allowed.", '<a href="'.admin_url( 'import.php', false ).'">'.__( 'Tools -> Import', 'yiw' ).'</a>' ), 'yiw' ); 
           yiw_error_message($error);
           return;
    }                
    
    if ( substr( $_FILES['import-file']['name'], -2 ) != 'gz' ) {
           $error = __( sprintf( "The file you have insert is not a valid file. Here only <b>.gz</b> file are allowed.", '<a href="'.admin_url( 'import.php', false ).'">'.__( 'Tools -> Import', 'yiw' ).'</a>' ), 'yiw' ); 
           yiw_error_message($error);
           return;
    }
    
    // get db encoded
    $content_file = file_get_contents( $_FILES['import-file']['tmp_name'] );
    
    $db = unserialize( base64_decode( gzuncompress( $content_file ) ) ); 
    
    array_walk_recursive( $db, 'yiw_convert_url', 'in_import' );
    //yiw_debug( $db );
    
    if( !is_array( $db ) )
        wp_die( __( 'An error encoured during during import. Please try again.', 'yiw' ) );
    
    // tables
    foreach( $yiw_wptables as $table )
    {
        yiw_string_( '<p></p><p><strong>', '// ' . $table, '</strong><br />' );
                                              
        // delete all row of each table
        $wpdb->query( "TRUNCATE TABLE {$wpdb->$table}" );
        yiw_string_( '', sprintf( __( 'Truncated %s table', 'yiw' ), $wpdb->$table ), '...<br />' );  
        
        // insert new data
        $error_data = array(); 
        foreach( $db[$table] as $id => $data )
        {                            
            if( $wpdb->insert( $wpdb->$table, $data ) )
                $insert = true;
            else
                $insert = false;   
            
            // save the ID that has error, to show.
            if( !$insert )
                $error_data[] = $id;         
        }                          
                                             
        if( $insert )
            yiw_string_( '', sprintf( __( 'Insert new values into %s table', 'yiw' ), $wpdb->$table ), '...</p>' );
        else
            yiw_string_( '', sprintf( __( 'Error during insert new values (IDs: %s), in %s table', 'yiw' ), implode( $error_data, ' ' ), $wpdb->$table ), '...</p>' );
    }
    
    yiw_string_( '<p></p><p><strong>', '// options', '</strong><br />' );
    
    // delete options               
    $theme = get_option( 'stylesheet' );
    $sql = "DELETE FROM {$wpdb->options} 
WHERE   option_name = '" . YIW_OPTIONS_DB . "_" . get_template() . "' 
OR      option_name = 'sidebars_widgets'
OR      option_name = 'show_on_front'
OR      option_name = 'page_on_front'
OR      option_name = 'page_for_posts'
OR      option_name LIKE 'widget%'
OR      option_name LIKE 'theme\_mods\_%'";  
    
    $sql .= ';';
    
    if( $wpdb->query( $sql ) )  
        yiw_string_( '', sprintf( __( 'Deleted value from %s table', 'yiw' ), $wpdb->options ), '...<br />' );
    else
        yiw_string_( '', sprintf( __( 'Error during deleting from %s table (SQL: %s)', 'yiw' ), $wpdb->options, $sql ), '...<br />' ); 
            
    
    // update options
    $error_data = array();
    $check = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_id = 1", ARRAY_A );
    foreach( $db['options'] as $id => $option )
    {                           
        if ( ! isset( $check['blog_id'] ) )
            unset( $option['blog_id'] );
          
        if( $wpdb->insert( $wpdb->options, $option ) )
            $insert = true; 
        else
            $insert = false;   
            
        // save the ID that has error, to show.
        if( !$insert )
            $wpdb->print_error();//$error_data[] = $option['option_name'];    
    }        
                    
    if( $insert )
        yiw_string_( '', sprintf( __( 'Insert new values, into %s table', 'yiw' ), $wpdb->options ), '...</p>' );   
    else
        yiw_string_( '', sprintf( __( 'Error during insert new values (IDs: %s), into %s table', 'yiw' ), implode( $error_data, ' ' ), $wpdb->options ), '...</p>' );   
                                   
     echo '</p>';    
    
    return true;
}         

function yiw_convert_url( &$item, $key, $type ) {

    $uploads = wp_upload_dir();
    $uploads_url = $uploads['baseurl'];
    
    $item = maybe_unserialize( $item );
    
    switch ( $type ) {
    
        case 'in_import' :              
            if ( is_array( $item ) ) {
                array_walk_recursive( $item, 'yiw_convert_url', $type ); 
                $item = serialize($item);  
            } else {     
                $item = str_replace( '%uploadsurl%', $uploads_url, $item );
                $item = str_replace( '%siteurl%', site_url(), $item );
            }
            break;
        
        case 'in_export' :              
            if ( is_array( $item ) ) {
                array_walk_recursive( $item, 'yiw_convert_url', $type );       
                $item = serialize($item);
            } else {               
                $item = str_replace( $uploads_url, '%uploadsurl%', $item );
                $item = str_replace( site_url(), '%siteurl%', $item ); 
            }
            break;
    
    }
        
}

function destroy( $dir ) 
{
    $mydir = opendir( $dir );
    
    while( false !== ( $file = readdir( $mydir ) ) ) {
        
        if( $file != "." && $file != ".." ) {
            
            chmod( $dir . $file, 0777 );
            
            if( is_dir( $dir . $file ) ) {
                chdir( '.' );
                destroy( $dir . $file . '/' );
                rmdir( $dir . $file ) or die( "couldn't delete $dir$file<br />" );
            }
            else
                unlink( $dir . $file ) or die( "couldn't delete $dir$file<br />" );
        }
    }
    closedir( $mydir );
}                   

function downloadRemoteFile( $url, $dir, $file_name = NULL )
{
    if( $file_name == NULL )
        $file_name = basename( $url );
        
    $url_stuff = parse_url( $url );
    $port = isset( $url_stuff['port'] ) ? $url_stuff['port'] : 80;

    $fp = fsockopen( $url_stuff['host'], $port );
    if( !$fp )
        return false;

    $query  = 'GET ' . $url_stuff['path'] . " HTTP/1.0\n";
    $query .= 'Host: ' . $url_stuff['host'];
    $query .= "\n\n";

    fwrite( $fp, $query );

    while ( $tmp = fread( $fp, 8192 ) )
        $buffer .= $tmp;               

    preg_match( '/Content-Length: ([0-9]+)/', $buffer, $parts );
    $file_binary = substr( $buffer, - $parts[1] ); 
    
    if( $file_name == NULL )
    {
        $temp = explode( ".", $url );
        $file_name = $temp[ count( $temp ) - 1 ];
    }
    
    $file_open = fopen( dirname( $dir ) . "/" . $file_name, 'w' );
    
    if( !$file_open )
        return false;
        
    fwrite( $file_open, $file_binary );
    fclose( $file_open );
    
    return true;
}  
?>