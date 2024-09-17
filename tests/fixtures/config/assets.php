<?php


(new XWP_Asset_Bundle('woosync'))
    ->with_base_dir(ASL_PLUGIN_PATH . 'dist')
    ->with_base_uri(plugins_url('dist', ASL_PLUGIN_BASENAME))
    ->with_priority(50)
    ->with_version('0.0.0')
    ->with_manifest(true)
    ->with_assets(
        array(
            'admin' => array(
                'js/awesome-notifications/awesome-notifications.js',
                'css/vendor/awesome-notifications.css',
                [
                    'src' => 'js/admin/woosync.js',
                    'id' => 'admin',
                ],
                'css/admin/woosync.css',
            ),
        )
    )
    ->load();


// return array(
//     'assets'   => array(
//         'admin' => array(
//             'js/awesome-notifications/awesome-notifications.js',
//             'css/vendor/awesome-notifications.css',
//             [
//                 'src' => 'js/admin/woosync.js',
//                 'handle' => 'woosync-admin',
//             ],
//             'css/admin/woosync.css',
//         ),
//     ),
//     'base_dir' => ASL_PLUGIN_PATH . 'dist',
//     'base_uri' => plugins_url( 'dist', ASL_PLUGIN_BASENAME ),
//     'id'       => 'woosync',
//     'manifest' => true,
//     'manifest' => 'assets.php',
//     'priority' => 50,
//     'version'  => '0.0.0',
// );
