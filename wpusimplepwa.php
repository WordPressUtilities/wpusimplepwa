<?php

/*
Plugin Name: WPU Simple PWA
Plugin URI: https://github.com/WordPressUtilities/WPUSimplePWA
Description: Turn your website into a simple PWA
Version: 0.1.0
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class WPUSimplePWA {
    private $plugin_version = '0.1.0';
    private $settings = array(
        'main_color' => '#336699',
        'default_icon' => false
    );

    public function __construct() {
        add_action('plugins_loaded', array(&$this, 'plugins_loaded'));
    }

    public function plugins_loaded() {
        $this->settings['default_icon'] = plugins_url('assets/icon-192x192.png', __FILE__);
        add_action('wp_head', array(&$this, 'wp_head'));
        add_filter('query_vars', array(&$this, 'query_vars'));
        add_action('template_redirect', array(&$this, 'template_redirect'));
    }

    public function wp_head() {
        echo '<meta name="apple-mobile-web-app-capable" content="yes" />';
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black" />';
        echo '<meta name="theme-color" content="' . $this->settings['main_color'] . '" />';
        echo '<meta name="msapplication-navbutton-color" content="' . $this->settings['main_color'] . '" />';
        echo '<link rel="apple-touch-icon" href="' . $this->settings['default_icon'] . '" />';
        echo '<link rel="manifest" href="' . site_url() . '/?wpupwa_mode=manifest">';
    }

    public function query_vars($query_vars) {
        $query_vars[] = 'wpupwa_mode';
        return $query_vars;
    }

    public function template_redirect() {
        if (!isset($_GET['wpupwa_mode'])) {
            return;
        }
        if ($_GET['wpupwa_mode'] == 'manifest') {
            $manifest = array(
                "name" => get_bloginfo('name'),
                "short_name" => get_bloginfo('name'),
                "start_url" => ".",
                "display" => "standalone",
                "theme_color" => $this->settings['main_color'],
                "background_color" => "#FFF",
                "icons" => array(
                    array(
                        "src" => $this->settings['default_icon'],
                        "sizes" => "192x192",
                        "type" => "image/png"
                    )
                )
            );
            echo json_encode($manifest);
            die;
        }
    }
}

$WPUSimplePWA = new WPUSimplePWA();
