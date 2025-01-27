<?php
/**
 * Plugin Name: Flutter OneSignal Notifications
 * Plugin URI: https://seusite.com
 * Description: Envia notificações push para aplicativos Flutter via OneSignal.
 * Version: 1.1
 * Author: Rafael Benetti
 * Author URI: https://creativewave.com.br
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Define o caminho do plugin
define('FLUTTER_ONESIGNAL_PATH', plugin_dir_path(__FILE__));

// Inclui os arquivos necessários
require_once FLUTTER_ONESIGNAL_PATH . 'includes/admin-page.php';
require_once FLUTTER_ONESIGNAL_PATH . 'includes/send-notification.php';

// Hook para enviar notificações automáticas ao publicar posts
add_action('publish_post', 'flutter_onesignal_send_post_notification', 10, 2);
