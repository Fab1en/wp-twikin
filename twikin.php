<?php
/*
 * Plugin Name: Twikin
 * Plugin URI: http://www.twikin.fr/
 * Author: Fabien Quatravaux
 * Author URI: http://fab1en.github.io
 * Description: Insère des fiches de jeux provenant de Twikin dans la médiathèque
 * Version: 1.0
 * License: GPL2
*/

// création du type mime "game"
require_once(sprintf("%s/models/game_mime_type.php", dirname(__FILE__)));

// pour afficher la ludothèque côté front
require_once(sprintf("%s/models/gamegallery_shortcode.php", dirname(__FILE__)));
require_once(sprintf("%s/models/game_shortcode.php", dirname(__FILE__)));

// hack permettant d'afficher l'image du jeux comme icône
require_once(sprintf("%s/models/display_icon.php", dirname(__FILE__)));

if(is_admin()){
    // gestion des jeux dans la médiathèque
    require_once(sprintf("%s/admin/mediatheque.php", dirname(__FILE__)));
    
    // gestion des jeux dans la lighbox d'édition des médias
    require_once(sprintf("%s/admin/media_editor.php", dirname(__FILE__)));
    
    // Menu "Ajouter un jeu"
    require_once(sprintf("%s/admin/add_game_menu.php", dirname(__FILE__)));
    
    // API twikin
    require_once(sprintf("%s/twikin_api.php", dirname(__FILE__)));
}

