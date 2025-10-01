<?php
// Configurações do e-mail
$para = "coffeindoor@gmail.com"; // <- Altere para o seu e-mail
$assunto = "Nova submissão de cafeteria";

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = htmlspecialchars($_POST["name"] ?? '');
    $instagram = htmlspecialchars($_POST["email"] ?? '');
    $mensagem = htmlspecialchars($_POST["message"] ?? '');

    // Validação simples
    if (empty($nome) || empty($instagram) || empty($mensagem)) {
        http_response_code(400);
        echo "Por favor, preencha todos os campos obrigatórios.";
        exit;
    }

    // Monta o corpo do e-mail
    $conteudo = "🟤 Nova Cafeteria Enviada:\n\n";
    $conteudo .= "☕ Nome da cafeteria: $nome\n";
    $conteudo .= "📸 Instagram: $instagram\n\n";
    $conteudo .= "📖 História/Descrição:\n$mensagem\n";

    // Configuração do e-mail (com anexos, se houver)
    $boundary = md5(time());
    $headers = "From: $nome <$instagram>\r\n";
    $headers .= "Reply-To: $instagram\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Corpo da mensagem em partes
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $conteudo . "\r\n";

    // Anexa arquivos se existirem
    if (!empty($_FILES['fotos']['name'][0])) {
        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['fotos']['name'][$key];
            $file_type = $_FILES['fotos']['type'][$key];
            $file_tmp = $_FILES['fotos']['tmp_name'][$key];

            if (is_uploaded_file($file_tmp)) {
                $file_content = chunk_split(base64_encode(file_get_contents($file_tmp)));

                $body .= "--$boundary\r\n";
                $body .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= $file_content . "\r\n";
            }
        }
    }

    $body .= "--$boundary--";

    // Envia o e-mail
    if (mail($para, $assunto, $body, $headers)) {
        echo "Cafeteria enviada com sucesso!";
    } else {
        http_response_code(500);
        echo "Erro ao enviar e-mail.";
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo "Método não permitido.";
}
