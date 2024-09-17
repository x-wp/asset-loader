<?php
/**
 * Plugin Name: Asset Loader Composer module
 * Description: A simple plugin to test the asset loader composer module.
 * Version:     1.0.0
 * Author:      Oblak Studio
 * Author URI:  https://oblak.studio
 */

use XWP\Dependency\Resources\Font;

defined( 'ASL_PLUGIN_FILE' )     || define( 'ASL_PLUGIN_FILE', __FILE__ );
defined( 'ASL_PLUGIN_BASENAME' ) || define( 'ASL_PLUGIN_BASENAME', plugin_basename( ASL_PLUGIN_FILE ), );
defined( 'ASL_PLUGIN_PATH' )     || define( 'ASL_PLUGIN_PATH', plugin_dir_path( ASL_PLUGIN_FILE ), );
defined( 'ASL_PLUGIN_URL' )      || define( 'ASL_PLUGIN_URL', plugin_dir_url( ASL_PLUGIN_FILE ), );

require_once __DIR__ . '/vendor/autoload.php';

add_action('init', function(){
    require __DIR__ . '/config/assets.php';
    // XWP_Asset_Loader::add_bundle(require __DIR__ . '/config/assets.php');
    // XWP_Asset_Loader::load_bundle(require __DIR__ . '/config/assets.php');
    // dump(XWP_Asset_Loader::get_bundle('woosync')['css/admin/woosync.css']->uri());
    // die;
}, 0);

add_filter('inline_style_args_woosync-awesome-notifications', function($params, $s) {
    // dump($params,$s);
    $params['data'] = 'body{ color: red !important; }';

    return $params;
}, 10, 2);

add_filter('inline_script_args_woosync-admin', function($params, $s) {
    $params['data'] = [
        'hello' => 'Hello World!',
        'world' => ['karina' => 'World Hello! Ћирилица'],
    ];

    $params['position'] = 'before';
    return $params;
}, 10, 2);

add_filter('localize_script_args_woosync-admin', function($params) {
    $params['object_name'] = 'woosync';
    $params['l10n'] = array(
        'hello' => 'Hello World!',
    );

    return $params;
}, 10, 2);

add_filter('woosync_can_enqueue_script', function($can, $name, $s) {
    if ('admin' === $name) {
        // $can = false;
    }

    return $can;
}, 10, 3);


add_action('init', function(){
    foreach (XWP_Asset_Loader::get_bundle('woosync')->get(Font::class) as $font) {
        $font->preload();
    }
}, 1000);
