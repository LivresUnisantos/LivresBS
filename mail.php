<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendMail($para,$assunto,$mensagemHTML,$mensagemTexto = "",$charset = "iso-8859-1") {
    $mail = new PHPMailer();
    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->IsSMTP(); // Define que a mensagem ser� SMTP
    $mail->Host = "mail.livresbs.com.br"; // Endere�o do servidor SMTP
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPAuth = true; // Autentica��o
    $mail->Port = 465; //465 = ssl | 587 = n�o ssl
    $mail->Username = 'naoresponder@livresbs.com.br'; // Usu�rio do servidor SMTP
    $mail->Password = 'a1b2@livr3#'; // Senha da caixa postal utilizada
    $mail->From = "naoresponder@livresbs.com.br"; 
    $mail->FromName = "Livres BS";
    $mail->AddAddress($para["email"], $para["nome"]);
    //$mail->AddCC('copia@dominio.com.br', 'Copia'); 
    //$mail->AddBCC('CopiaOculta@dominio.com.br', 'Copia Oculta');
    $mail->IsHTML(true); // Define que o e-mail será enviado como HTML
    $mail->CharSet = $charset; // Charset da mensagem (opcional)
    $mail->Subject  = $assunto; // Assunto da mensagem
    $mail->Body = $mensagemHTML;
    $mail->AltBody = $mensagemTexto; //
    //$mail->AddAttachment("e:\home\login\web\documento.pdf", "novo_nome.pdf");
    $enviado = $mail->Send();
    $mail->ClearAllRecipients();
    $mail->ClearAttachments();
    if ($enviado) {
        return true;
    } else {
        return $mail->ErrorInfo;
    }
}
?>