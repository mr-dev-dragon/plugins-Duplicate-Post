<?php

namespace Pentiminax\DuplicatePost;

use Pentiminax\DuplicatePost\Controller\AdminController;

class PentiminaxPlugin
{
	const TRANSIENT_DUPLICATE_POST_ACTIVATED = 'pentiminax_duplicate_post_activated';

	public function __construct(string $file)
	{
		register_activation_hook($file, [$this, 'plugin_activation']);
		add_action('admin_notices', [$this, 'notice_activation']);

		if (is_admin()) {
			$adminController = new AdminController();
		}
	}

	public function plugin_activation(): void
	{
		set_transient(self::TRANSIENT_DUPLICATE_POST_ACTIVATED, true);
	}

	public function notice_activation(): void
	{
		if (get_transient(self::TRANSIENT_DUPLICATE_POST_ACTIVATED)) {
			self::render('notices', [
				'message' => "Merci d'avoir activé <strong>Pentiminax Duplicate Post</strong> !"
			]);
			delete_transient(self::TRANSIENT_DUPLICATE_POST_ACTIVATED);
		}
	}

	public static function render(string $name, array $args = []): void
	{
		extract($args);

		$file = PENTIMINAX_PLUGIN_DIR . "views/$name.php";

		ob_start();

		include_once($file);

		echo ob_get_clean();
	}
}