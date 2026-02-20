<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/lib/Mailer.php';

echo "<h1>Teste de Envio de Email</h1>";

try {
    $mailer = new Mailer();
    $config = require __DIR__ . '/config/email.php';
    
    $to = $config['username']; // Send to yourself
    $subject = "Teste de Email - Produção";
    $body = "Se você recebeu este email, o envio está funcionando corretamente no servidor.";

    echo "<p>Tentando enviar email para: <strong>$to</strong></p>";

    if ($mailer->send($to, $subject, $body)) {
        echo "<h2 style='color:green'>SUCESSO! Email enviado.</h2>";
        echo "<p>Verifique sua caixa de entrada (e spam).</p>";
    } else {
        echo "<h2 style='color:red'>ERRO ao enviar email.</h2>";
        echo "<p>Verifique os validadores de erro no log ou na tela se houver (ative o debug do PHPMailer se necessário).</p>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red'>Exceção Capturada:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
