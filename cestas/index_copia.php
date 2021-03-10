<?php
require '../_app/Config.inc.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title><?= SITE_NAME ?></title>
        <meta name="description" content="<?= SITE_DESC; ?>"/>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
        <meta name="robots" content="noindex, nofollow"/>

        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro:300,500' rel='stylesheet' type='text/css'>
        <link rel="base" href="<?= BASE; ?>/Painel">
        <link rel="shortcut icon" href="<?= BASE; ?>/passaro-icon.png" />

        <link rel="stylesheet" href="<?= BASE; ?>/Painel/_js/datepicker/datepicker.min.css"/>
        <link rel="stylesheet" href="<?= BASE; ?>/Painel/_css/reset.css"/>
        <link rel="stylesheet" href="<?= BASE; ?>/Painel/_css/painel.css?t=<?= time(); ?>"/>
        <link rel="stylesheet" href="<?= BASE; ?>/Painel/_css/painel-860.css?t=<?= time(); ?>" media="screen and (max-width: 860px)"/>
        <link rel="stylesheet" href="<?= BASE; ?>/Painel/_css/painel-480.css?t=<?= time(); ?>" media="screen and (max-width: 500px)"/>
        <link rel="stylesheet" href="<?= BASE; ?>/Painel/_css/bootcss/fonticon.css"/>

        <script src="<?= BASE; ?>/Painel/_js/jquery.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/jquery.form.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/painel.js?t=<?= time() ?>"></script>

        <script src="<?= BASE; ?>/Painel/_js/tinymce/tinymce.min.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/maskinput.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/highcharts.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/datepicker/datepicker.min.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/datepicker/datepicker.pt-BR.js"></script>
    </head>
    <body class="dashboard_main">
        <section class="secao_cesta">
            <header class="dashboard_header">
                <div class="dashboard_header_title">
                    <h1 class="icon-cart">Cesta</h1>
                    <p class="dashboard_header_breadcrumbs">
                        &raquo; <?= SITE_NAME; ?>
                        <span class="crumb">/</span>
                        <a title="<?= SITE_NAME; ?>" href="<?= BASE; ?>"><?= SITE_NAME; ?></a>
                        <span class="crumb">/</span>
                        <a title="Cestas" href="<?= BASE; ?>/Cestas/">Consultar outro consumidor</a>
                    </p>
                </div>
            </header>
            <header class="dashboard_header">
                <div class="dashboard_header_title">
                    <h1 class="icon-user">Nome do consumidor</h1>
                </div>
            </header>
        </section>
        <section class="panel secao_cesta" style="padding: 20px 0;">
            <table class="tableCestas" cellspacing="0">
                <tr>
                    <th class="thCestas" colspan="3" style="background-color: #1aa4db; color: #fff; text-align: center; text-transform: uppercase;">
                        Cesta Semanal
                    </th>
                </tr>
                <tr>
                    <th class="thCestas">
                        Produto
                    </th>
                    <th class="thCestas">
                        Quantidade
                    </th>
                    <th class="thCestas">
                        Valor
                    </th>
                </tr>

                <?php for ($i = 0; $i < 20; $i++): ?>
                    <tr class="trCestas">
                        <td class="tdCestas">Nome do produto</td>
                        <td class="tdCestas">00 unidades</td>
                        <td class="tdCestas">R$ X,xx</td>
                    </tr>
                <?php endfor; ?>
            </table>
        </section>

    </body>
</html>