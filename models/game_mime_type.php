<?php

// ajouter un nouveau type de média
add_filter( 'post_mime_types', 'twikin_add_game_mime_type');
function twikin_add_game_mime_type($mimes){
    $mimes['game'] = array(
        __('Jeux', 'twikin'),
        __('Gérer les jeux Twikin', 'twikin'),
        _n_noop( 'Jeu <span class="count">(%s)</span>', 'Jeux <span class="count">(%s)</span>', 'twikin' )
    );
    return $mimes;
}

// taxonomy pour ce nouveau type de média : le type de jeu
add_action( 'init', 'twikin_game_taxonomies' );
function twikin_game_taxonomies(){

    register_taxonomy('kind', 'attachment:game', array(
        'labels' => array(
            'name' => __( 'Types de jeu', 'twikin' ),
            'singular_name' => __( 'Type de jeu', 'twikin' ),
            'search_items' => __( 'Chercher dans les types de jeu', 'twikin' ),
            'popular_items' => null,
            'all_items' => __( 'Tous les types de jeu', 'twikin' ),
            'edit_item' => __( 'Éditer le type de jeu', 'twikin' ),
            'update_item' => __( 'Mettre à jour le type de jeu', 'twikin' ),
            'add_new_item' => __( 'Ajouter un nouveau type de jeu', 'twikin' ),
            'new_item_name' => __( 'Nom du nouveau type de jeu', 'twikin' ),
            'separate_items_with_commas' => null,
            'add_or_remove_items' => null,
            'choose_from_most_used' => null,
        ),
        'hierarchical' => true,
        'rewrite' => array( 'slug' => 'type' ),
        'show_admin_column' => true,
    ));
    
}
