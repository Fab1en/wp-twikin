<?php

// afficher la miniature du jeu dans la page d'édition
add_action('edit_form_after_title', 'twikin_display_thumbnail');
function twikin_display_thumbnail(){
    if ( isset( $_GET['post'] ) )
        $attachment = get_post((int) $_GET['post']);
    elseif ( isset( $_POST['post_ID'] ) )
     	$attachment = get_post((int) $_GET['post_ID']);
    else
        return;
     	
    if($attachment->post_type != 'attachment') return;
    
    list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
    if($type == "game"){
        // tester s'il existe un fichier image attaché
        $image = get_post_meta($attachment->ID, '_wp_attachment_metadata', true);
        if(isset($image['file'])){
            echo '<img class="thumbnail" src="'.$image['file'].'" style="max-width:100%">';
        }
        
        if($subtype == "twikin") {
            // lien vers la fiche Twikin
            echo '<div><a href="'.$attachment->guid.'" target="_blank">'.__('voir la fiche sur Twikin', 'twikin').'</a></div>';
        }
    }
}

// ajouter des infos supplémentaires dans la page d'édtion
add_action('add_meta_boxes', 'twikin_infos_box', 10, 2);
function twikin_infos_box($post_type, $attachment){
    if($post_type == 'attachment') {
        list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
        if($type == 'game'){
            add_meta_box('twikin_infos', __('Fiche technique', 'twikin'), 'twikin_infos_box_inside', 'attachment', 'side', 'high');
        }
    }
}
function twikin_infos_box_inside($attachment){
    $infos = get_post_meta($attachment->ID, 'twikin', true);
    ?>
        <div class="misc-pub-section">joueurs: <strong><?php echo $infos->min_players; ?>-<?php echo $infos->max_players; ?></strong></div>
        <div class="misc-pub-section">temps: <strong><?php echo $infos->length; ?> min</strong></div>
        <div class="misc-pub-section">age: <strong><?php echo $infos->min_age; ?> et +</strong></div>
    <?php
}

// afficher l'icône dans la Médiathèque
add_filter('wp_mime_type_icon', 'twikin_game_icon', 10, 3);
function twikin_game_icon($icon, $mime, $post_id){
    
    list( $type, $subtype ) = explode( '/', $mime );
    if($type == 'game') {
    
        // tester s'il existe un fichier image attaché
        $image = get_post_meta($post_id, '_wp_attachment_metadata', true);
        if(isset($image['sizes']) && isset($image['sizes']['thumbnail'])){
            $image = $image['sizes']['thumbnail']['file'];
        }

        
        if($image && strpos($image, 'http://www.twikin.fr/medias/image/') !== false){
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
function twikin_icon_dir_plugin(){
    return plugin_dir_path(__FILE__).'img';
}
function twikin_icon_dir_url(){
    return 'http://www.twikin.fr/medias/image/1/3/2';
}

// supprimer le hack pour passer le test d'existance de l'icône
add_filter('wp_get_attachment_image_attributes', 'twikin_reset_icon_dir');
function twikin_reset_icon_dir($arg){
    remove_filter('icon_dir', 'twikin_icon_dir_plugin');
    remove_filter('icon_dir', 'twikin_icon_dir_url');
    return $arg;
}
