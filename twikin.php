<?php
/*
 * Plugin Name: Twikin
 * Plugin URI: http://www.twikin.fr/
 * Author: Fabien Quatravaux
 * Author URI: http://fab1en.github.io
 * Description: Insère des fiches de jeux provenant de Twikin dans la médiathèque
 * Version: 1.0
*/

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

// définir l'icône
add_filter('wp_mime_type_icon', 'twikin_game_icon', 10, 3);
function twikin_game_icon($icon, $mime, $post_id){
    
    if($mime == 'game/twikin') {
        // TODO: gérer l'affichage de la miniature par défaut
        $icon = plugins_url('img/default-game.png', __FILE__);
        
        // hack pour passer le test d'existance de l'icône
        add_filter('icon_dir', 'twikin_icon_dir');
    }
    return $icon;
}

// hack pour passer le test d'existance de l'icône
function twikin_icon_dir($path){
    return plugin_dir_path(__FILE__).'img';
}

// supprimer le hack pour passer le test d'existance de l'icône
add_filter('wp_get_attachment_image_attributes', 'twikin_reset_icon_dir');
function twikin_reset_icon_dir($in){
    remove_filter('icon_dir', 'twikin_icon_dir');
    return $in;
}

