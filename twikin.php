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
	            $('#twikin-title').keyup(function(e){
	                clearTimeout(timer);
	                timer = setTimeout(callApi, 1000);
	            });
	            function callApi(){
	                $.get(ajaxurl, {action: 'twikin-api', search: $('#twikin-title').val()}, function(data){
	                    console.log(data);
	                    if(data.error) {
	                        $('#twikin-api-result').text('Erreur : '+data.error);
	                    } else {
	                        if(data.results && data.results.length){
	                            $('#twikin-api-result').html('<ol></ol>');
	                            for(r in data.results){
	                                $('#twikin-api-result').append('<li><a target="_blank" href="http://www.twikin.fr/jeux/'+data.results[r].id+'"><img src="'+data.results[r].media_url+'"/> '+data.results[r].name+'</a></li>');
	                            }
	                        }
	                    }
	                });
	            }
	        });
	    </script>
	</div><?php
}

add_action('wp_ajax_twikin-api', 'twikin_api_call');
function twikin_api_call(){
    $response = wp_remote_get('http://www.twikin.fr/api/game/search/'.$_GET['search']);
    if(!is_wp_error($response)){
        header('Content-type: application/json');
        echo $response['body'];
    } else {
        print_r($response);
    }
    die();
}

