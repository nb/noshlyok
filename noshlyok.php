<?php
/*
Plugin Name: Без шльокавица
Description: Не позволява изпращането на коментари без поне един кирилишки символ
Author: Николай Бачийски
Version: 0.01
License: The source code below is in the public domain
*/ 

function noshlyok_shlyok_allowed($post_id) {
	$allow_shlyok = get_post_meta($post_id, 'allow_shlyok', true);
	return ($allow_shlyok == 1);
}

function noshlyok_verify($comment_data) {
    if (!preg_match("/[а-яА-Я]/u", $comment_data['comment_content'])) {
		if (!noshlyok_shlyok_allowed($comment_data['comment_post_ID'])) {
			wp_die("<p>Моля, пишете на кирилица!</p><p>Please use cyrillic letters for your comment!</p><p><a href='javascript:history.back()'>&laquo; Назад / Back</a></p>");
		}
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
	global $post;
	$checked = '';
	if (isset($post->ID) && $post->ID > 0 && noshlyok_shlyok_allowed($post->ID)) {
		$checked = 'checked="checked"';
	}
?>
	<fieldset id="cyr" class="dbx-box">
		<h3 class="dbx-handle">Шльокавица</h3>
		<div class="dbx-content">
			<p>
				<input name="allow_shlyok" type="checkbox" id="allow_shlyok_check" <?php echo $checked; ?>/>
				<label for="allow_shlyok_check">Позволяване на шльокавица в коментарите по тази публикация</label>
			</p>
		</div>
	</fieldset>
<?php
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

add_action('dbx_post_sidebar', 'noshlyok_post_sidebar');
add_action('dbx_page_sidebar', 'noshlyok_post_sidebar');
add_action('save_post', 'noshlyok_save_post');

add_filter('preprocess_comment', 'noshlyok_verify');

