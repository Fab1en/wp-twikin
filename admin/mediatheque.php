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

