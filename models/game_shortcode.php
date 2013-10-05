<?php
add_shortcode('game', 'twikin_game_shortcode');

function twikin_game_shortcode($attr){
    $post = get_post();
    
    extract(shortcode_atts(array(
        'id' => $post->ID,
    ), $attr));

    $twikin_infos = get_post_meta($id, 'twikin', true);

    // récupérer l'image dans sa meilleure qualité
    $thumb = get_post_meta($id, '_wp_attachment_metadata', true);
    $twikin_infos->media_url = $thumb['file'];

    // formater la durée
    if($twikin_infos->length) {
        if($twikin_infos->length < 59){
            $length = $twikin_infos->length . ' min';
        } else {
            $length = floor($twikin_infos->length/60) . 'h';
            if($twikin_infos->length % 60 > 0){
                $length .= $twikin_infos->length % 60;
            }
        }
        $twikin_infos->length = $length;
    }

    // formater la description
    if(!empty($twikin_infos->description)){
        $desc = trim($twikin_infos->description);
        $desc = preg_replace('|\n\s*\n|', '</p><p>', $desc);
        $desc = str_replace("\n", '<br/>', $desc);
        $twikin_infos->description = '<p>'.$desc.'</p>';
    }

    // logo twikin
    $twikin_infos->logo = plugins_url('img/Logo-Twikin-Blue.png', __FILE__);

    // afficher le template Mustache
    require_once(sprintf("%s/../vendor/mustache.php", dirname(__FILE__)));
    $mustache = new Mustache_Engine(array('loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../templates/')));
    $tpl = $mustache->loadTemplate('game');
    return $tpl->render($twikin_infos);
}
