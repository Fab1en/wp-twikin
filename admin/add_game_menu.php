<?php

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
	</div><?php
}

add_action('admin_print_scripts-media_page_twikin_addgame', 'twikin_addgame_script');
function twikin_addgame_script(){
    wp_enqueue_script('twikin-addgame-menu', plugins_url('js/addgame_menu.js', __FILE__), array(), false, true);
}
