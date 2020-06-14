<?php

/*
Plugin Name: WPU Simple PWA
Plugin URI: https://github.com/WordPressUtilities/WPUSimplePWA
Description: Turn your website into a simple PWA
Version: 0.5.0
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class WPUSimplePWA {
    private $plugin_version = '0.5.0';
    private $settings = array(
        'main_color' => '#336699',
        'background_color' => '#336699',
        'default_icon' => false,
        'default_icon_m' => false,
        'default_splash' => false
    );

    public function __construct() {
        add_action('plugins_loaded', array(&$this, 'plugins_loaded'));
    }

    public function plugins_loaded() {
        $this->settings['default_icon'] = plugins_url('assets/icons/icon-192x192.png', __FILE__);
        $this->settings['default_icon_m'] = plugins_url('assets/icons/icon-256x256.png', __FILE__);
        $this->settings['default_splash'] = plugins_url('assets/icons/icon-512x512.png', __FILE__);
        $this->settings = apply_filters('wpusimplepwa_settings', $this->settings);
        add_action('wp_head', array(&$this, 'wp_head'));
        add_filter('query_vars', array(&$this, 'query_vars'));
        add_action('template_redirect', array(&$this, 'router'));
    }

    public function wp_head() {
        echo '<meta name="apple-mobile-web-app-capable" content="yes" />';
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black" />';
        echo '<meta name="theme-color" content="' . $this->settings['main_color'] . '" />';
        echo '<meta name="msapplication-navbutton-color" content="' . $this->settings['main_color'] . '" />';
        echo '<link rel="apple-touch-icon" href="' . $this->settings['default_icon'] . '" />';
        echo '<link rel="manifest" href="' . site_url() . '/?wpupwa_mode=manifest">';
        echo '<script>';
        echo 'var serviceWorkerUrl = "' . site_url() . '/?wpupwa_mode=worker";';
        include dirname(__FILE__) . '/assets/main.js';
        echo '</script>';
    }

    public function query_vars($query_vars) {
        $query_vars[] = 'wpupwa_mode';
        return $query_vars;
    }

    public function router() {
        if (!isset($_GET['wpupwa_mode'])) {
            return;
        }
        if ($_GET['wpupwa_mode'] == 'worker') {
            $this->trigger_worker();
        }
        if ($_GET['wpupwa_mode'] == 'manifest') {
            $this->trigger_manifest();
        }
    }

    public function trigger_worker() {

        $files = apply_filters('wpusimplepwa_worker_files', array('/'));
        $cachename = apply_filters('wpusimplepwa_worker_cachename', 'wpusimplepwa' . $this->plugin_version);

        header('content-type:application/x-javascript');
        echo 'var cacheName = "' . esc_attr($cachename) . '";';
        echo 'var appHost = "' . esc_attr(parse_url(site_url(), PHP_URL_HOST)) . '";';
        echo 'var appShellFiles = ' . json_encode($files) . ';';
        include dirname(__FILE__) . '/assets/service-worker.js';
        die;
    }

    public function trigger_manifest() {
        $manifest = array(
            "name" => get_bloginfo('name'),
            "short_name" => get_bloginfo('name'),
            "start_url" => ".",
            "display" => "standalone",
            "theme_color" => $this->settings['main_color'],
            "background_color" => $this->settings['background_color'],
            "icons" => array(
                array(
                    "src" => $this->settings['default_icon'],
                    "sizes" => "192x192",
                    "type" => "image/png"
                ),
                array(
                    "src" => $this->settings['default_icon_m'],
                    "sizes" => "256x256",
                    "type" => "image/png"
                ),
                array(
                    "src" => $this->settings['default_splash'],
                    "sizes" => "512x512",
                    "type" => "image/png"
                )
            )
        );

        $site_icon = get_option('site_icon');
        if (is_numeric($site_icon)) {
            $manifest['icons'] = array();

            $data = wp_get_attachment_metadata($site_icon);
            $upload_dir = wp_upload_dir();
            $dirname = dirname($data['file']);
            $base_url = trailingslashit($upload_dir['baseurl']) . $dirname . '/';
            foreach ($data['sizes'] as $size) {
                $manifest['icons'][] = array(
                    "src" => $base_url . $size['file'],
                    "sizes" => $size['width'].'x'.$size['height'],
                    "type" => $size['mime-type']
                );
            }
        }

        $manifest = apply_filters('wpusimplepwa_manifest', $manifest);

        header('content-type:application/manifest+json');
        echo json_encode($manifest);
        die;
    }
}

$WPUSimplePWA = new WPUSimplePWA();
