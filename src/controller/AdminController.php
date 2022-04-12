<?php

namespace Pentiminax\DuplicatePost\Controller;

use Pentiminax\DuplicatePost\PentiminaxPlugin;

class AdminController
{
	const REDIRECT_TO_LIST = 0;
	const REDIRECT_TO_EDIT = 1;

	public function __construct()
	{
		$this->init_hooks();
	}

	public function init_hooks(): void
	{
		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_init', [$this, 'admin_init']);
		add_action('post_row_actions', [$this, 'duplicate_post_actions'], 10, 2);
		add_action('admin_action_duplicate', [$this, 'duplicate_post']);
	}

	public function admin_menu(): void
	{
		add_options_page('DuplicatePost', 'Duplicate Post', 'manage_options', 'duplicate_post', [$this, 'config_page']);
	}

	public function config_page(): void
	{
		PentiminaxPlugin::render('config');
	}

	public function admin_init(): void
	{
		register_setting('duplicate_post_general', 'duplicate_post_general');
		add_settings_section('duplicate_post_main', null, null, 'duplicate_post');
		add_settings_field('redirect_to', 'Rediriger vers après avoir cliqué sur "Dupliquer"', [$this, 'redirect_to_render'], 'duplicate_post', 'duplicate_post_main');
	}

	public function redirect_to_render(): void
	{
		$general_options = get_option('duplicate_post_general', [
			'redirect_to' => 0
		]);

		$selectedValue = $general_options['redirect_to'];

		?>
		<select name="duplicate_post_general[redirect_to]">
			<option value="<?= self::REDIRECT_TO_LIST ?>" <?= selected(self::REDIRECT_TO_LIST, $selectedValue) ?>>Vers la liste des articles</option>
			<option value="<?= self::REDIRECT_TO_EDIT ?>" <?= selected(self::REDIRECT_TO_EDIT, $selectedValue) ?>>Vers l'écran de modification de l'article dupliqué</option>
		</select>
		<?php
	}

	public function duplicate_post_actions(array $actions, \WP_Post $post): array
	{
		if (current_user_can('edit_posts')) {
			$post_id = $post->ID;
			$actions['duplicate_post'] = "<a href='admin.php?post=$post_id&action=duplicate'>Dupliquer</a>";
		}

		return $actions;
	}

	public function duplicate_post()
	{
		$general_options = get_option('duplicate_post_general', [
			'redirect_to' => 0
		]);

		$redirect_to = intval($general_options['redirect_to']);

		$post_id = (isset($_GET['post'])) ? intval($_GET['post']) : 0;

		$this->verify_request($post_id);

		$post = get_post($post_id);

		if (!$post) {
			wp_die("Une erreur est survenue. L'article $post_id est introuvable !", "Article introuvable !");
		}

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;

		$post_data = [
			'post_author' => $user_id,
			'post_content' => $post->post_content,
			'post_title' => $post->post_title,
			'post_excerpt' => $post->post_excerpt,
			'post_status' => $post->post_status,
			'comment_status' => $post->comment_status,
			'ping_status' => $post->ping_status,
			'post_password' => $post->post_password,
			'to_ping' => $post->to_ping,
			'post_parent' => $post->post_parent,
			'menu_order' => $post->menu_order
		];

		$new_post_id = wp_insert_post($post_data);

		if ($redirect_to === self::REDIRECT_TO_LIST) {
			wp_safe_redirect(admin_url('edit.php'));
		} elseif ($redirect_to === self::REDIRECT_TO_EDIT) {
			wp_safe_redirect(admin_url("post.php?post=$new_post_id&action=edit"));
		}
	}

	public function verify_request($post_id)
	{
		$referer = wp_get_referer();
		$location = $referer ? : get_site_url();

		if (!current_user_can('edit_posts', $post_id)) {
			wp_safe_redirect($location);
		}
	}
}