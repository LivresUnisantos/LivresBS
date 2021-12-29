<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/config-pix.php';

use \App\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

function PixGetCopiaCola($chavepix, $descricao, $conta_nome, $conta_cidade, $valor, $pixid) {

    $obPayload = (new Payload)->setPixKey($chavepix)
                          ->setDescription($descricao)
                          ->setMerchantName($conta_nome)
                          ->setMerchantCity($conta_cidade)
                          ->setAmount($valor)
                          ->setTxid($pixid);
    
    //Código copia/cola
    $payloadQrCode = $obPayload->getPayload();
    return $payloadQrCode;

}

function PixPrintQRCode($pixCopiaCola) {
    $obQrCode = new QrCode($pixCopiaCola);

    //IMAGEM DO QRCODE
    $image = (new Output\Png)->output($obQrCode,400);

    return '<img src="data:image/png;base64, '.base64_encode($image).'">';
}

$pixcc = PixGetCopiaCola(PIX_KEY, 'Pagamento do pedido X', PIX_MERCHANT_NAME, PIX_MERCHANT_CITY, 0.1, 'codpixtst1');
?>
<h1>QR CODE ESTÁTICO DO PIX</h1>

<br>

<?php echo PixPrintQRCode($pixcc); ?>

<br><br>

Código pix:<br>
<strong><?=$pixcc?></strong>
<?php
/*
//INSTANCIA PRINCIPAL DO PAYLOAD PIX
$obPayload = (new Payload)->setPixKey(PIX_KEY)
                          ->setDescription('Pagamento do pedido 123456')
                          ->setMerchantName(PIX_MERCHANT_NAME)
                          ->setMerchantCity(PIX_MERCHANT_CITY)
                          ->setAmount(0.10)
                          ->setTxid('TestePgt2');

//CÓDIGO DE PAGAMENTO PIX
$payloadQrCode = $obPayload->getPayload();

//QR CODE
$obQrCode = new QrCode($payloadQrCode);

//IMAGEM DO QRCODE
$image = (new Output\Png)->output($obQrCode,400);

?>

<h1>QR CODE ESTÁTICO DO PIX</h1>

<br>

<img src="data:image/png;base64, <?=base64_encode($image)?>">

<br><br>

Código pix:<br>
<strong><?=$payloadQrCode?></strong>

*/