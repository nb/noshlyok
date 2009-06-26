<?php
/*
Plugin Name: Без шльокавица
Plugin URI: http://wordpress.org/extend/plugins/noshlyok/
Description: Не позволява изпращането на коментари без поне един кирилишки символ
Author: Николай Бачийски
Author URI: http://nb.niichavo.org/
Version: 0.04
License: The source code below is in the public domain
*/ 

function noshlyok_die( $message ) {
    if ( defined('DOING_AJAX') && DOING_AJAX )
        die($message);
    else
        wp_die('<p>'.$message."</p><p><a href='javascript:history.back()'>&laquo; Назад / Back</a></p>");
}

function noshlyok_shlyok_allowed($post_id) {
	$allow_shlyok = get_post_meta($post_id, 'allow_shlyok', true);
	return ($allow_shlyok == 1);
}

function noshlyok_verify($comment_data) {
	
	if ( noshlyok_shlyok_allowed($comment_data['comment_post_ID']) ) {
		return $comment_data;
	}
	
	// do not allow comments without cyrillic characters
    if (!preg_match("/[а-яА-Я]/u", $comment_data['comment_content'])) {
		noshlyok_die("Моля, пишете на кирилица!</p><p>Please use cyrillic letters for your comment!");
    }
	// do not allow .ru emails and web sites
	if (!preg_match('|^http://|', $comment_data['comment_author_url'])) {
		$comment_data['comment_author_url'] = 'http://'.$comment_data['comment_author_url'];
	}
    $parsed = parse_url($comment_data['comment_author_url']);
	error_log(print_r($parsed, true));
    if ( (isset($parsed['host']) && preg_match("|\.ru$|", $parsed['host'])) ||
			preg_match("|\.ru$|", $comment_data['comment_author_email']) || 
			preg_match("/ы|ё|э/i", $comment_data['comment_content'])) {
        noshlyok_die('Рускиий &mdash; нет!');
	}

    return $comment_data;
}


function noshlyok_allow($post_id) {
	if (!noshlyok_shlyok_allowed($post_id)) {
		add_post_meta($post_id, 'allow_shlyok', 1, true);
	}
}

function noshlyok_disallow($post_id) {
	if (noshlyok_shlyok_allowed($post_id)) {
		delete_post_meta($post_id, 'allow_shlyok');
	}
}

function noshlyok_post_sidebar() {
?>
	<fieldset id="cyr" class="dbx-box postbox">
		<h3 class="dbx-handle">Шльокавица</h3>
		<div class="dbx-content">
			<?php noshlyok_box_contents(); ?>
		</div>
	</fieldset>
<?php
}

function noshlyok_box_contents($output = true) {
	global $post;
	$checked = '';
	if (isset($post->ID) && $post->ID > 0 && noshlyok_shlyok_allowed($post->ID)) {
		$checked = 'checked="checked"';
	}
	$contents = <<<HTML
				<input name="allow_shlyok" type="checkbox" id="allow_shlyok_check" $checked />
				<label for="allow_shlyok_check">Позволяване на шльокавица в коментарите по тази публикация</label>
HTML;
	if ($output) echo $contents;
	return $contents;
}

function noshlyok_save_post($post_id) {
	if (isset($_POST['allow_shlyok'])) {
		if ('on' == $_POST['allow_shlyok']) {
			noshlyok_allow($post_id);
		}
	} else {
		noshlyok_disallow($post_id);
	}
}

function noslyok_register_boxes() {
	if (function_exists('add_meta_box')) {
		add_meta_box('noshlyok', 'Шльокавица', 'noshlyok_box_contents', 'post');
		add_meta_box('noshlyok', 'Шльокавица', 'noshlyok_box_contents', 'page');
	} else {
		add_action('dbx_post_sidebar', 'noshlyok_post_sidebar');
		add_action('dbx_page_sidebar', 'noshlyok_post_sidebar');
	}
}

function noshlyok_init() {
	if (is_admin()) {
		add_action('admin_menu', 'noslyok_register_boxes');
	}
	add_action('save_post', 'noshlyok_save_post');
	add_filter('preprocess_comment', 'noshlyok_verify');
}

add_action('init', 'noshlyok_init');


