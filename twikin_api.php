<?php

// relayer les requêtes vers l'API twikin
add_action('wp_ajax_twikin-api', 'twikin_api_call');
function twikin_api_call(){
    $response = wp_remote_get('http://www.twikin.fr/api/game/search/'.$_REQUEST['search']);
    if(!is_wp_error($response)){
        
        // ajouter des infos sur la médiathèque
        $resp_json = json_decode($response['body']);
        if(property_exists($resp_json, 'results')){
        
            // vérifier si le jeu existe déjà dans la médiathèque
            $match = false;
            foreach($resp_json->results as $i => $game){
                $existing = new WP_Query(array(
                    'post_type' => 'attachment',
                    'post_mime_type' => 'game',
                    'post_status' => 'inherit',
                    'meta_key' => 'twikin_id',
                    'meta_value' => $game->id,
                ));
                
                if($existing->have_posts()){
                    $match = true;
                    $resp_json->results[$i]->wpid = $existing->post->ID;
                    $resp_json->results[$i]->wp_edit = admin_url("post.php?post=".$existing->post->ID."&action=edit");
                }
            }
            
            if($match) $response['body'] = json_encode($resp_json);
        }
        
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
                add_post_meta($id, 'twikin_id', $response->id);
                add_post_meta($id, '_wp_attachment_metadata', array(
                    'width' => 160,
                    'height' => 160,
                    'file' => str_replace('c.80.80', '', $response->media_url),
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
