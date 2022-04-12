<?php

/*
Plugin Name: ahmed Duplicate Post
Plugin URI: 
Description: Permet de dupliquer le articles de WordPress !
Version: 0.1
Author: Pentiminax
Author URI: 
*/

use Pentiminax\DuplicatePost\PentiminaxPlugin;

if ( ! defined( 'ABSPATH' ) )
	exit;

define('PENTIMINAX_PLUGIN_DIR', plugin_dir_path(__FILE__));

require PENTIMINAX_PLUGIN_DIR . 'vendor/autoload.php';

$plugin = new PentiminaxPlugin(__FILE__);