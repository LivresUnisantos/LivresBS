<?php

/*
 * URL DO SISTEMA
 */
if ($_SERVER['HTTP_HOST'] == 'localhost'):
    define('BASE', 'http://localhost'); //Url raiz do site no localhost
else:
    if ($_SERVER["HTTP_HOST"] == "livresbs.com.br"):
        define('BASE', 'https://livresbs.com.br'); //Url raiz do site no servidor
    else:
        define('BASE', 'https://hendersonmele.com.br'); //Url raiz do site no servidor
    endif;
endif;

//DINAMYC THEME
if (!empty($_SESSION['WC_THEME'])):
    define('THEME', $_SESSION['WC_THEME']); //template do site
else:
    define('THEME', ''); //template do site
endif;

/*
 * PATCH CONFIG
 */
define('INCLUDE_PATH', BASE . '/themes/' . THEME); //Geral de inclusão (Não alterar)
define('REQUIRE_PATH', 'themes/' . THEME); //Geral de inclusão (Não alterar)


/*
 * ADMIN CONFIG
 */
define('ADMIN_NAME', 'Admin Livres BS');  //Nome do painel de controle (Work Control)
define('ADMIN_DESC', 'Sistema de administração do Livres da Baixada Santista!'); //Descrição do painel de controle (Work Control)
define('ADMIN_MODE', 2); //1 = website / 2 = e-commerce / 3 = Imobi / 4 = EAD
define('ADMIN_WC_CUSTOM', 0); //Habilita menu e telas customizadas
define('ADMIN_MAINTENANCE', 0); //Manutenção
define('ADMIN_VERSION', '3.1.3');

/*
 * SITE CONFIG
 */
define('SITE_NAME', 'LivresBS'); //Nome do site do cliente
define('SITE_DESC', 'O Livres é uma rede que organiza grupos de produtores, consumidores, feiras e lojas.'); //Nome do site do cliente


/*
 * E-MAIL SERVER
 * Consulte estes dados com o serviço de hospedagem
 */
define('MAIL_HOST', 'smtp.gmail.com'); //Servidor de e-mail
define('MAIL_PORT', '587'); //Porta de envio
define('MAIL_USER', ''); //E-mail de envio
define('MAIL_SMTP', ''); //E-mail autenticador do envio (Geralmente igual ao MAIL_USER, exceto em serviços como AmazonSES, sendgrid...)
define('MAIL_PASS', ''); //Senha do e-mail de envio
define('MAIL_SENDER', ''); //Nome do remetente de e-mail
define('MAIL_MODE', ''); //Encriptação para envio de e-mail [0 não parametrizar / tls / ssl] (Padrão = tls)
define('MAIL_TESTER', ''); //E-mail de testes (DEV)

/*
 * LEVEL CONFIG
 * Configura permissões do painel de controle!
 */
define('LEVEL_PEDIDOS', 1);
