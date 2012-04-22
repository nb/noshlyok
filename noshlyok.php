<?php
/*
Plugin Name: Без шльокавица
Plugin URI: http://wordpress.org/extend/plugins/noshlyok/
Description: Не позволява изпращането на коментари без поне един кирилишки символ
Author: Николай Бачийски
Author URI: http://nikolay.bg/
Version: 0.06
License: The source code below is in the public domain
*/

class Noshlyok {
	
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}
	
	function init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_boxes' ) );
		}
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_filter( 'preprocess_comment', array( $this, 'verify' ) );
	}
	
	function verify( $comment_data ) {

		$top_level_domain_re = '/\.(ru|ua)$/';
		$cyrillic_letters_re = '/[а-яА-Я]/u';
		$no_russian_letters_re = '/ы|ё|э|Ы|Ё|Э/';

		if ( $this->shlyok_allowed( $comment_data['comment_post_ID'] ) ) {
			return $comment_data;
		}

		// do not allow comments without cyrillic characters
		if ( !preg_match( $cyrillic_letters_re, $comment_data['comment_content'] ) ) {
			$this->die_with_message( 'Моля, пишете на кирилица!</p><p>Please, use cyrillic letters for your comment!' );
		}
		// do not allow .ru emails and web sites
		if ( !preg_match( '|^http://|', $comment_data['comment_author_url'] ) && $comment_data['comment_author_url'] ) {
			$comment_data['comment_author_url'] = 'http://' . $comment_data['comment_author_url'];
		}
		$parsed = @parse_url( $comment_data['comment_author_url'] );
		if ( ( isset($parsed['host'] ) && preg_match( $top_level_domain_re, $parsed['host'] ) ) ||
				preg_match( $top_level_domain_re, $comment_data['comment_author_email'] ) ||
				preg_match( $no_russian_letters_re, $comment_data['comment_content'] ) ||
				preg_match( $no_russian_letters_re, $comment_data['comment_author'] ) ) {
			$this->die_with_message( 'Русский &mdash; нет!' );
		}

		return $comment_data;
	}

	function die_with_message( $message ) {
		if ( defined('DOING_AJAX') && DOING_AJAX )
			die( $message );
		else
			wp_die( '<p>' . $message . "</p><p><a href='javascript:history.back()'>&laquo; Назад / Back</a></p>" );
	}
	
	function shlyok_allowed( $post_id ) {
		$allow_shlyok = get_post_meta( $post_id, 'allow_shlyok', true );
		return ( $allow_shlyok == 1 );
	}
	
	function register_boxes() {
		if ( function_exists( 'add_meta_box' ) ) {
			add_meta_box( 'noshlyok', 'Шльокавица', array( $this, 'box_contents' ), 'post' );
			add_meta_box( 'noshlyok', 'Шльокавица', array( $this, 'box_contents' ), 'page' );
		} else {
			add_action( 'dbx_post_sidebar', array( $this, 'post_sidebar' ) );
			add_action( 'dbx_page_sidebar', array( $this, 'post_sidebar' ) );
		}
	}

	function save_post( $post_id ) {
		if ( isset( $_POST['allow-shlyok'] ) ) {
			if ( 'on' == $_POST['allow-shlyok'] ) {
				$this->allow($post_id);
			}
		} else {
			$this->disallow($post_id);
		}
	}

	function allow( $post_id ) {
		if ( !$this->shlyok_allowed( $post_id ) ) {
			add_post_meta( $post_id, 'allow_shlyok', 1, true );
		}
	}

	function disallow( $post_id ) {
		if ( $this->shlyok_allowed( $post_id ) ) {
			delete_post_meta( $post_id, 'allow_shlyok' );
		}
	}
	
	function post_sidebar() {
	?>
		<fieldset id="cyr" class="dbx-box postbox">
			<h3 class="dbx-handle">Шльокавица</h3>
			<div class="dbx-content">
				<?php $this->box_contents(); ?>
			</div>
		</fieldset>
	<?php
	}

	function box_contents( $output = true ) {
		global $post;
		$checked = '';
		if ( isset( $post->ID ) && $post->ID > 0 && $this->shlyok_allowed( $post->ID ) ) {
			$checked = 'checked="checked"';
		}
		$contents = <<<HTML
					<input name="allow-shlyok" type="checkbox" id="allow-shlyok-check" $checked />
					<label for="allow-shlyok-check">Позволяване на шльокавица в коментарите по тази публикация</label>
HTML;
		if ( $output ) echo $contents;
		return $contents;
	}	
}

$GLOBALS['noshlyok'] = new Noshlyok;
