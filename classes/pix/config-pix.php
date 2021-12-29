<?php
/**
 * Configurações do projeto Pix
 */

//DADOS GERAIS DO PIX (DINÂMICO E ESTÁTICO)
define('PIX_KEY','682ab121-de84-43a0-a0d9-0c52496e7b29');
define('PIX_MERCHANT_NAME','Mateus Santos Barros');
define('PIX_MERCHANT_CITY','Sao Paulo');
// define('PIX_KEY','36831129803');
// define('PIX_MERCHANT_NAME','Henderson Rodrigues Mele');
// define('PIX_MERCHANT_CITY','Santos');

//DADOS DA API PIX (DINÂMICO)
define('API_PIX_URL','https://api-pix-h.urldoseupsp.com.br"');
define('API_PIX_CLIENT_ID','Client_id_100120310230123012301230120312');
define('API_PIX_CLIENT_SECRET','Client_secret_100120310230123012301230120312');
define('API_PIX_CERTIFICATE',__DIR__.'/files/certificates/certificado.pem');
