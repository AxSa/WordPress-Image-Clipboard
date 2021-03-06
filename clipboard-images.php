<?php
/*
	Plugin Name: Clipboard Images
	Description: Support paste images from clipboard for posts & comments (based on filereader.js)
	Author: CasePress
	Version: 0.2
	Author URI: https://github.com/casepress/WordPress-Image-Clipboard
 */

class Clipboard_Images
{
	public function __construct()
	{
		add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
		add_action('wp_enqueue_scripts', array(&$this, 'scripts'));

		add_action('init', array(&$this, 'init'));

		add_action('wp_ajax_cbimages_save', array(&$this, 'save_image'));
		add_action('wp_ajax_nopriv_cbimages_save', array(&$this, 'save_image'));
	}

	public function scripts()
	{
		wp_enqueue_script('filereader.js', plugins_url("js/filereader.min.js", __FILE__), array('jquery'));

		if (is_admin())
		{
			wp_enqueue_script('admin-cb-images', plugins_url("js/admin.js", __FILE__), array('filereader.js'));
		}
		else
		{
			wp_enqueue_script('fronted-cb-images', plugins_url("js/fronted.js", __FILE__), array('filereader.js'));
			// hook for ajax url
			wp_localize_script('fronted-cb-images', 'cbimages', array('ajaxurl' => admin_url('admin-ajax.php')));  
		}
	}

	public function init()
	{
		add_filter('mce_external_plugins', array(&$this, 'mce_plugin'));
	}

	public function mce_plugin($plugins)
	{
		$plugins['cbimages'] = plugins_url("js/editor_plugin_src.js", __FILE__);
		return $plugins;
	}

	public function save_image()
	{
		$img = $_POST['img'];
		$tmp_img = explode(";", $img);
		$img_header = explode('/', $tmp_img[0]);
		$ext = $img_header[1];

		$imgtitle = mt_rand(111,999);
		$imgtitle .= '.'.$ext;

		$uploads = wp_upload_dir($time = null); 
		$filename = wp_unique_filename($uploads['path'], $imgtitle);
		
		file_put_contents($uploads['path'].'/'.$filename, file_get_contents('data://'.$img));

		echo json_encode(array('file' => $uploads['url'] .'/'. $filename));
		die();
	}
}

$clipboard_images = new Clipboard_Images();

