<?php
/*
 * Plugin Name: Twikin
 * Plugin URI: http://www.twikin.fr/
 * Author: Fabien Quatravaux
 * Author URI: http://fab1en.github.io
 * Description: Insère des fiches de jeux provenant de Twikin dans la médiathèque
 * Version: 1.0
*/

add_action( 'init', 'twikin_game_taxonomies' );
function twikin_game_taxonomies(){

    register_taxonomy('kind', 'attachment:game', array(
        'labels' => array(
            'name' => __( 'Type de jeu' ),
            'singular_name' => __( 'Type de jeu' ),
            'search_items' => __( 'Chercher dans les types de jeu' ),
            'popular_items' => null,
            'all_items' => __( 'Tous les types de jeu' ),
            'edit_item' => __( 'Éditer le type de jeu' ),
            'update_item' => __( 'Mettre à jour le type de jeu' ),
            'add_new_item' => __( 'Ajouter un nouveau type de jeu' ),
            'new_item_name' => __( 'Nom du nouveau type de jeu' ),
            'separate_items_with_commas' => null,
            'add_or_remove_items' => null,
            'choose_from_most_used' => null,
        ),
        'hierarchical' => true,
        'rewrite' => array( 'slug' => 'type' ),
        'show_admin_column' => true,
    ));
    
}

// ajouter un nouveau type de média
add_filter( 'post_mime_types', 'twikin_add_game_mime_type');
function twikin_add_game_mime_type($mimes){
    $mimes['game/twikin'] = array(
        'Jeu Twikin',
        'Gérer les jeux Twikin',
        _n_noop( 'Jeu <span class="count">(%s)</span>', 'Jeux <span class="count">(%s)</span>' )
    );
    
    return $mimes;
}

add_action('edit_form_after_title', 'twikin_display_thumbnail');
function twikin_display_thumbnail(){
    if ( isset( $_GET['post'] ) )
        $post = get_post((int) $_GET['post']);
    elseif ( isset( $_POST['post_ID'] ) )
     	$post = get_post((int) $_GET['post_ID']);
     	
    if($post && $post->post_mime_type == "game/twikin"){
        // tester s'il existe un fichier image attaché
        $image = get_post_meta($post->ID, '_wp_attachment_metadata', true);
        if(isset($image['file'])){
            echo '<img class="thumbnail" src="'.$image['file'].'" style="max-width:100%">';
        }
        
        // lien vers la fiche Twikin
        echo '<div><a href="'.$post->guid.'" target="_blank">voir la fiche sur Twikin</a></div>';
    }
}

// définir l'icône
add_filter('wp_mime_type_icon', 'twikin_game_icon', 10, 3);
function twikin_game_icon($icon, $mime, $post_id){
    
    if($mime == 'game/twikin') {
    
        // tester s'il existe un fichier image attaché
        $image = get_post_meta($post_id, '_wp_attachment_metadata', true);
        if(isset($image['sizes']) && isset($image['sizes']['thumbnail'])){
            $image = $image['sizes']['thumbnail']['file'];
        }
        
        if($image){
            $icon = $image;
            // hack pour passer le test d'existance de l'icône
            add_filter('icon_dir', 'twikin_icon_dir_url');
        } else {
            // miniature par défaut
            $icon = plugins_url('img/default-game.png', __FILE__);
            
            // hack pour passer le test d'existance de l'icône
            add_filter('icon_dir', 'twikin_icon_dir_plugin');
        }
    }
    return $icon;
}

// hack pour passer le test d'existance de l'icône
function twikin_icon_dir_plugin($path){
    return plugin_dir_path(__FILE__).'img';
}
function twikin_icon_dir_url($path){
    return 'http://www.twikin.fr/medias/image/1/3/2';
}

// supprimer le hack pour passer le test d'existance de l'icône
add_filter('wp_get_attachment_image_attributes', 'twikin_reset_icon_dir');
function twikin_reset_icon_dir($in){
    remove_filter('icon_dir', 'twikin_icon_dir_plugin');
    remove_filter('icon_dir', 'twikin_icon_dir_url');
    return $in;
}

add_action('admin_menu', 'twikin_setup_addgame_menu');
function twikin_setup_addgame_menu(){
    add_media_page('Ajouter un jeu', 'Ajouter un jeu', 'upload_files', 'twikin_addgame', 'twikin_addgame_menu_page');
}
function twikin_addgame_menu_page(){
    ?><div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>Ajouter un jeu</h2>
	    <form method="post" action="<?php echo admin_url('upload.php?page=twikin_addgame'); ?>">
	        <label>Titre:<input id="twikin-title" value="" type="text" /></label>
	        <div id="twikin-api-result"></div>
	    </form>
	    <script>
	        jQuery(function($){
	            var timer;
	            $('#twikin-title').on('keyup change input', function(e){
	                clearTimeout(timer);
	                if($(e.currentTarget).val().length > 2) {
	                    timer = setTimeout(callApi, 1000);
	                }
	            });
	            function callApi(){
	                $.get(ajaxurl, {action: 'twikin-api', search: $('#twikin-title').val()}, function(data){
	                    if(data.error) {
	                        $('#twikin-api-result').text('Erreur : '+data.error);
	                    } else {
	                        if(data.results && data.results.length){
	                            $('#twikin-api-result').html('<ol></ol>');
	                            for(r in data.results){
	                                var item;
	                                item = '<li>';
	                                item += '<a target="_blank" href="http://www.twikin.fr/jeux/'+data.results[r].id+'">';
	                                item += '   <img src="'+data.results[r].media_url+'"/> ';
	                                item +=     data.results[r].name;
	                                item += '</a>';
	                                item += '<button class="twikin-add-game" data-twikinid="'+data.results[r].id+'">Ajouter</button>';
	                                item += '</li>';
	                                
	                                $('#twikin-api-result').append(item);
	                            }
	                        } else {
	                            $('#twikin-api-result').text('Aucun résultat');
	                        }
	                    }
	                });
	            }
	            
	            $('#twikin-api-result').on('click', '.twikin-add-game', function(e){
	                $.post(ajaxurl, {action: 'twikin-add', gameid: $(e.currentTarget).attr('data-twikinid')});
	                return false;
	            });
	        });
	    </script>
	</div><?php
}

add_action('wp_ajax_twikin-api', 'twikin_api_call');
function twikin_api_call(){
    $response = wp_remote_get('http://www.twikin.fr/api/game/search/'.$_REQUEST['search']);
    if(!is_wp_error($response)){
        header('Content-type: application/json');
        echo $response['body'];
    } else {
        print_r($response);
    }
    die();
}

add_action('wp_ajax_twikin-add', 'twikin_add_game');
function twikin_add_game(){
    // TODO commencer par chercher si le jeu existe déjà dans la Médiathèque ?
    
    // récupérer toutes les infos de Twikin
    $response = wp_remote_get('http://www.twikin.fr/api/game/view/'.$_REQUEST['gameid']);
    if(!is_wp_error($response)){
    
        $response = json_decode($response['body']);
        if(property_exists($response, 'success') && $response->success){
            
            $user = wp_get_current_user();
            
            $game = array(
                'post_title' => $response->name,
                'post_content' => $response->description,
                'post_type' => 'attachment',
                'post_mime_type' => 'game/twikin',
                'post_author' => $user->ID,
                'post_status' => 'inherit',
                'guid' => 'http://www.twikin.fr/jeux/'.$response->id,
            );
            
            if(property_exists($response, 'kind_name')){
                $kind = get_term_by('name', $response->kind_name, 'kind');
                if(!$kind ){
                    $term = wp_insert_term($response->kind_name, 'kind');
                    $kind = get_term($term['term_id'], 'kind');
                }
                $game['tax_input'] = array('kind' => array($kind->term_id));
            }
            
            // ajouter le jeu en base
            $id = wp_insert_post($game);
            if(!is_wp_error($id)){
                $response->media_url = str_replace('https', 'http', $response->media_url);
                // sauvegarder les informations supplémentaires
                add_post_meta($id, 'twikin', $response);
                add_post_meta($id, '_wp_attachment_metadata', array(
                    'width' => 160,
                    'height' => 160,
                    'file' => str_replace('c.80.80', '.160.160', $response->media_url),
                    // TODO: prendre la taille des miniatures dans les options
                    'sizes' => array(
                        'thumbnail' => array(
                            'width' => 80,
                            'height' => 60,
                            'file' => str_replace('c.80.80', '.80.60', $response->media_url),
                        ),
                    ),
                ));
            }
        }
        
    } else {
        print_r($response);
    }
    die();
}

