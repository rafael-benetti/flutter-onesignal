<?php
defined('ABSPATH') || exit;

// Envia notificações manuais
add_action('admin_post_send_onesignal_notification', 'flutter_onesignal_send_notification');

function flutter_onesignal_send_notification()
{
    if (!current_user_can('manage_options')) {
        wp_die('Acesso negado');
    }

    $title = sanitize_text_field($_POST['title']);
    $message = sanitize_textarea_field($_POST['message']);
    $segment = sanitize_text_field($_POST['segment']) ?: 'All';

    flutter_onesignal_push($title, $message, $segment);
}

// Envia notificações automáticas ao publicar posts
add_action('publish_post', 'flutter_onesignal_send_post_notification', 10, 2);
function flutter_onesignal_send_post_notification($post_id, $post)
{
    // Remove o hook temporariamente para evitar chamadas duplicadas
    remove_action('publish_post', 'flutter_onesignal_send_post_notification', 10);

    // Verifica se o post está publicado
    if ($post->post_status !== 'publish') {
        return;
    }

    // Verifica se já enviou notificação para este post
    if (get_post_meta($post_id, '_onesignal_notification_sent', true)) {
        return;
    }

    // Agenda o envio para 3 minutos no futuro
    wp_schedule_single_event(time() + 3600, 'flutter_onesignal_delayed_notification', [$post_id]);

    // Adiciona o hook novamente
    add_action('publish_post', 'flutter_onesignal_send_post_notification', 10, 2);
}

// Hook para a tarefa agendada
add_action('flutter_onesignal_delayed_notification', 'flutter_onesignal_delayed_notification_handler');
function flutter_onesignal_delayed_notification_handler($post_id)
{
    $post = get_post($post_id);
    if (!$post) {
        return;
    }

    $title = $post->post_title;
    $message = '📲 Notícia completa no App!';
    $segment = 'All';

    // Define o link para abrir no app
    $url = 'https://seuapp.com.br/noticia/' . $post_id;

    // Envia a notificação
    $success = flutter_onesignal_push($title, $message, $segment, $url);

    if ($success) {
        // Marca o post como "notificação enviada"
        update_post_meta($post_id, '_onesignal_notification_sent', true);
    }
}


// Função principal para enviar notificações via OneSignal
function flutter_onesignal_push($title, $message, $segment, $url = null)
{
    $url = 'https://onesignal.com/api/v1/notifications';

    // Credenciais do OneSignal
    $api_key = 'os_v2_app_3cb55ni3qnaaxc5x67b4mk4l5vgnlkvxjglu2uvsaqmfpfsd4e3u7efa7koph2up7negnd7gki3ggvtujvvpj4kfk63vnp7r7afg6hy';
    $app_id = 'd883deb5-1b83-400b-8bb7-f7c3c62b8bed';

    // Corpo da requisição
    $body = [
        'app_id' => $app_id,
        'included_segments' => [$segment],
        'headings' => ['en' => $title],
        'contents' => ['en' => $message],
        'url' => $url, // Adiciona o link
    ];

    // Envia a notificação
    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Basic ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode($body),
    ]);

    if (is_wp_error($response)) {
        error_log('Erro ao enviar notificação: ' . $response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        error_log('Erro ao enviar notificação: ' . wp_remote_retrieve_body($response));
        return false;
    }

    return true;
}

