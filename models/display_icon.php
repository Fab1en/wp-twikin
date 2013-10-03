<?php

// afficher l'image du jeu comme icône
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
