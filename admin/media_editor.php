<?php

// afficher le nom du jeu plutôt que son ID dans la lighbox d'édition de médias
add_filter('wp_prepare_attachment_for_js', 'twikin_wpmedia_infos', 10, 3);
function twikin_wpmedia_infos($response, $attachment, $meta){
    
    list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
	if($type == "game"){
		$response['filename'] = $attachment->post_title;
	}
	return $response;
}

// insertion d'un jeu dans un article
add_filter('media_send_to_editor', 'twikin_insert_game_into_post', 10, 3);
function twikin_insert_game_into_post($html, $send_id, $attachment){
	$post = get_post($send_id);
	list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
	if($type == 'game'){
		$html = "<figure>";
		$html .= wp_get_attachment_image($send_id, 'thumbnail', true);
		$html .= "<figcaption>".$post->post_title."</figcaption>";
		$html .= "</figure>";
	}
	return $html;
}

// insertion d'une ludothèque dans un article
add_action('admin_enqueue_scripts', 'twikin_add_mediaeditor_menu');
function twikin_add_mediaeditor_menu(){
    global $pagenow;
    if($pagenow == 'post.php') {
        wp_enqueue_script('twikin-media-menu', plugins_url('js/mediaeditor_menu.js', __FILE__), array('media-views'), false, true);
    }
}
 
add_filter('media_view_strings', 'twikin_mediamenu_title', 10, 2);
function twikin_mediamenu_title($strings,  $post){
    $strings['createGameGalleryTitle'] = __('Créer une ludothèque', 'twikin');
    $strings['editGameGalleryTitle'] = __('Éditer la ludothèque', 'twikin');
    $strings['addToGameGallery'] = __('Ajouter à la ludothèque', 'twikin');
    $strings['cancelGameGallery'] = __('&#8592; Annuler la ludothèque', 'twikin');
    $strings['newGameGalleryButton'] = __('Créer une nouvelle ludothèque', 'twikin');
    $strings['insertGameGalleryButton'] = __('insérer la ludothèque', 'twikin');
    $strings['addToGameGalleryButton'] = __('ajouter à la ludothèque', 'twikin');
    return $strings;
}
