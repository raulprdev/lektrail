<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return $url;
    }
}

if (!function_exists('rest_url')) {
    function rest_url($path = '') {
        return 'http://example.com/wp-json/' . ltrim($path, '/');
    }
}

if (!function_exists('esc_sql')) {
    function esc_sql($data) {
        return addslashes($data);
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value = null, $url = '') {
        if (is_array($key)) {
            $url = $value ?: '';
            $params = $key;
        } else {
            $params = [$key => $value];
        }
        $query = http_build_query($params);
        return $url . (strpos($url, '?') !== false ? '&' : '?') . $query;
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
}