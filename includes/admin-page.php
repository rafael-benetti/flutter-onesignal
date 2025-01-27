<?php
// Adiciona a página ao menu do WordPress
add_action('admin_menu', function () {
    add_menu_page(
        'Enviar Notificações',
        'Flutter OneSignal',
        'manage_options',
        'flutter-onesignal',
        'flutter_onesignal_admin_page',
        'dashicons-megaphone'
    );
});

// Função que renderiza a página de administração
function flutter_onesignal_admin_page()
{
    ?>
    <div class="wrap">
        <h1>Enviar Notificação Push</h1>
        <?php if (isset($_GET['sent']) && $_GET['sent'] == 'success') : ?>
            <div class="notice notice-success is-dismissible">
                <p>Notificação enviada com sucesso!</p>
            </div>
        <?php elseif (isset($_GET['sent']) && $_GET['sent'] == 'error') : ?>
            <div class="notice notice-error is-dismissible">
                <p>Erro ao enviar a notificação. Verifique os logs para mais detalhes.</p>
            </div>
        <?php endif; ?>
        <form method="post" action="admin-post.php">
            <input type="hidden" name="action" value="send_onesignal_notification">
            <table class="form-table">
                <tr>
                    <th><label for="title">Título:</label></th>
                    <td><input type="text" id="title" name="title" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="message">Mensagem:</label></th>
                    <td><textarea id="message" name="message" class="large-text" rows="5" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="segment">Segmento (opcional):</label></th>
                    <td><input type="text" id="segment" name="segment" class="regular-text" placeholder="Ex: All"></td>
                </tr>
            </table>
            <?php submit_button('Enviar Notificação'); ?>
        </form>
    </div>
    <?php
}
