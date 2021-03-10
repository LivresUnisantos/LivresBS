<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

require '../../_app/Config.inc.php';
require_once "../../includes/autoloader.inc.php";
require_once '../../twig/autoload.php';
$livres = new Livres();
$calendario = new Calendario();
$loader = new \Twig\Loader\FilesystemLoader('../../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

include "../acesso.php";

$getView = filter_input(INPUT_GET, 'lbs', FILTER_DEFAULT);

//PEGAR DATA
$StartDate = (!empty($_SESSION['data_consulta']) ? $_SESSION['data_consulta'] : date("Y-m-d 00:00"));
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title><?= ADMIN_NAME ?></title>
        <meta name="description" content="<?= ADMIN_DESC; ?>"/>
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
        <!--<link rel="stylesheet" href="../painel.css">-->
        <style>
            .navbar {
                position: relative;
                display: -ms-flexbox;
                display: flex;
                -ms-flex-wrap: wrap;
                flex-wrap: wrap;
                -ms-flex-align: center;
                align-items: center;
                -ms-flex-pack: justify;
                justify-content: space-between;
                padding: 0.5rem 1rem;
            }

            @media (max-width: 991.98px) {
                .navbar-expand-lg > .container,
                .navbar-expand-lg > .container-fluid, .navbar-expand-lg > .container-sm, .navbar-expand-lg > .container-md, .navbar-expand-lg > .container-lg, .navbar-expand-lg > .container-xl {
                    padding-right: 0;
                    padding-left: 0;
                }
            }

            @media (min-width: 992px) {
                .navbar-expand-lg {
                    -ms-flex-flow: row nowrap;
                    flex-flow: row nowrap;
                    -ms-flex-pack: start;
                    justify-content: flex-start;
                }
                .navbar-expand-lg .navbar-nav {
                    -ms-flex-direction: row;
                    flex-direction: row;
                }
                .navbar-expand-lg .navbar-nav .dropdown-menu {
                    position: absolute;
                }
                .navbar-expand-lg .navbar-nav .nav-link {
                    padding-right: 0.5rem;
                    padding-left: 0.5rem;
                }
                .navbar-expand-lg > .container,
                .navbar-expand-lg > .container-fluid, .navbar-expand-lg > .container-sm, .navbar-expand-lg > .container-md, .navbar-expand-lg > .container-lg, .navbar-expand-lg > .container-xl {
                    -ms-flex-wrap: nowrap;
                    flex-wrap: nowrap;
                }
                .navbar-expand-lg .navbar-collapse {
                    display: -ms-flexbox !important;
                    display: flex !important;
                    -ms-flex-preferred-size: auto;
                    flex-basis: auto;
                }
                .navbar-expand-lg .navbar-toggler {
                    display: none;
                }
            }

            .navbar-light .navbar-brand {
                color: rgba(0, 0, 0, 0.9);
            }

            .navbar-light .navbar-brand:hover, .navbar-light .navbar-brand:focus {
                color: rgba(0, 0, 0, 0.9);
            }

            .navbar-light .navbar-nav .nav-link {
                color: rgba(0, 0, 0, 0.5);
                text-decoration: none;
            }

            .navbar-light .navbar-nav .nav-link:hover, .navbar-light .navbar-nav .nav-link:focus {
                color: rgba(0, 0, 0, 0.7);
            }

            .navbar-light .navbar-nav .nav-link.disabled {
                color: rgba(0, 0, 0, 0.3);
            }

            .navbar-light .navbar-nav .show > .nav-link,
            .navbar-light .navbar-nav .active > .nav-link,
            .navbar-light .navbar-nav .nav-link.show,
            .navbar-light .navbar-nav .nav-link.active {
                color: rgba(0, 0, 0, 0.9);
            }

            .navbar-light .navbar-toggler {
                color: rgba(0, 0, 0, 0.5);
                border-color: rgba(0, 0, 0, 0.1);
            }

            .navbar-light .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%280, 0, 0, 0.5%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }

            .navbar-light .navbar-text {
                color: rgba(0, 0, 0, 0.5);
            }

            .navbar-light .navbar-text a {
                color: rgba(0, 0, 0, 0.9);
            }

            .navbar-light .navbar-text a:hover, .navbar-light .navbar-text a:focus {
                color: rgba(0, 0, 0, 0.9);
            }


            .bg-light {
                background-color: #f8f9fa !important;
            }

            a.bg-light:hover, a.bg-light:focus,
            button.bg-light:hover,
            button.bg-light:focus {
                background-color: #dae0e5 !important;
            }


            .navbar-brand {
                display: inline-block;
                padding-top: 0.3125rem;
                padding-bottom: 0.3125rem;
                margin-right: 1rem;
                font-size: 1.25rem;
                line-height: inherit;
                white-space: nowrap;
                text-decoration: none;
            }

            .navbar-brand:hover, .navbar-brand:focus {
                text-decoration: none;
            }

            .navbar-toggler {
                padding: 0.25rem 0.75rem;
                font-size: 1.25rem;
                line-height: 1;
                background-color: transparent;
                border: 1px solid transparent;
                border-radius: 0.25rem;
            }

            .navbar-toggler:hover, .navbar-toggler:focus {
                text-decoration: none;
            }

            .navbar-toggler-icon {
                display: inline-block;
                width: 1.5em;
                height: 1.5em;
                vertical-align: middle;
                content: "";
                background: no-repeat center center;
                background-size: 100% 100%;
            }

            .collapse:not(.show) {
                display: none;
            }

            .navbar-collapse {
                -ms-flex-preferred-size: 100%;
                flex-basis: 100%;
                -ms-flex-positive: 1;
                flex-grow: 1;
                -ms-flex-align: center;
                align-items: center;
            }


            .navbar-nav {
                display: -ms-flexbox;
                display: flex;
                -ms-flex-direction: column;
                flex-direction: column;
                padding-left: 0;
                margin-bottom: 0;
                list-style: none;
            }

            .navbar-nav .nav-link {
                padding-right: 0;
                padding-left: 0;
            }

            .navbar-nav .dropdown-menu {
                position: static;
                float: none;
            }

            .mr-auto,
            .mx-auto {
                margin-right: auto !important;
            }
            .dropup,
            .dropright,
            .dropdown,
            .dropleft {
                position: relative;
            }

            .dropdown-toggle {
                white-space: nowrap;
            }

            .dropdown-toggle::after {
                display: inline-block;
                margin-left: 0.255em;
                vertical-align: 0.255em;
                content: "";
                border-top: 0.3em solid;
                border-right: 0.3em solid transparent;
                border-bottom: 0;
                border-left: 0.3em solid transparent;
            }

            .dropdown-toggle:empty::after {
                margin-left: 0;
            }

            .dropdown-menu {
                position: absolute;
                top: 100%;
                left: 0;
                z-index: 1000;
                display: none;
                float: left;
                min-width: 10rem;
                padding: 0.5rem 0;
                margin: 0.125rem 0 0;
                font-size: 1rem;
                color: #212529;
                text-align: left;
                list-style: none;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid rgba(0, 0, 0, 0.15);
                border-radius: 0.25rem;
            }

            .dropdown-menu-left {
                right: auto;
                left: 0;
            }

            .dropdown-menu-right {
                right: 0;
                left: auto;
            }

            @media (min-width: 576px) {
                .dropdown-menu-sm-left {
                    right: auto;
                    left: 0;
                }
                .dropdown-menu-sm-right {
                    right: 0;
                    left: auto;
                }
            }

            @media (min-width: 768px) {
                .dropdown-menu-md-left {
                    right: auto;
                    left: 0;
                }
                .dropdown-menu-md-right {
                    right: 0;
                    left: auto;
                }
            }

            @media (min-width: 992px) {
                .dropdown-menu-lg-left {
                    right: auto;
                    left: 0;
                }
                .dropdown-menu-lg-right {
                    right: 0;
                    left: auto;
                }
            }
            .dropdown-menu[x-placement^="top"], .dropdown-menu[x-placement^="right"], .dropdown-menu[x-placement^="bottom"], .dropdown-menu[x-placement^="left"] {
                right: auto;
                bottom: auto;
            }

            .dropdown-divider {
                height: 0;
                margin: 0.5rem 0;
                overflow: hidden;
                border-top: 1px solid #e9ecef;
            }

            .dropdown-item {
                display: block;
                width: 100%;
                padding: 0.25rem 1.5rem;
                clear: both;
                font-weight: 400;
                color: #212529;
                text-align: inherit;
                white-space: nowrap;
                background-color: transparent;
                border: 0;
                text-decoration: none;
            }

            .dropdown-item:hover, .dropdown-item:focus {
                color: #16181b;
                text-decoration: none;
                background-color: #f8f9fa;
            }

            .dropdown-item.active, .dropdown-item:active {
                color: #fff;
                text-decoration: none;
                background-color: #007bff;
            }

            .dropdown-item.disabled, .dropdown-item:disabled {
                color: #6c757d;
                pointer-events: none;
                background-color: transparent;
            }

            .dropdown-menu.show {
                display: block;
            }

            .dropdown-header {
                display: block;
                padding: 0.5rem 1.5rem;
                margin-bottom: 0;
                font-size: 0.875rem;
                color: #6c757d;
                white-space: nowrap;
            }

            .dropdown-item-text {
                display: block;
                padding: 0.25rem 1.5rem;
                color: #212529;
            }


            .nav-link {
                display: block;
                padding: 0.5rem 1rem;
            }

            .nav-link:hover, .nav-link:focus {
                text-decoration: none;
            }

            .nav-link.disabled {
                color: #6c757d;
                pointer-events: none;
                cursor: default;
            }

            .dropdown-item {
                display: block;
                width: 100%;
                padding: 0.25rem 1.5rem;
                clear: both;
                font-weight: 400;
                color: #212529;
                text-align: inherit;
                white-space: nowrap;
                background-color: transparent;
                border: 0;
            }

            .dropdown-item:hover, .dropdown-item:focus {
                color: #16181b;
                text-decoration: none;
                background-color: #f8f9fa;
            }

            .dropdown-item.active, .dropdown-item:active {
                color: #fff;
                text-decoration: none;
                background-color: #007bff;
            }

            .dropdown-item.disabled, .dropdown-item:disabled {
                color: #6c757d;
                pointer-events: none;
                background-color: transparent;
            }
            .btnData {
                display: inline-block;
                vertical-align: middle;
                font-size: 0.8em;
                cursor: pointer;
                padding: 6px 12px;
                background: #00B494;
                color: #fff;
                text-decoration: none;
                text-align: center;
                border: none;
                border-radius: 3px;
                -moz-border-radius: 3px;
                -webkit-border-radius: 3px;
                text-shadow: 1px 1px #555;
            }
        </style>
        <script src="<?= BASE; ?>/Painel/_js/jquery.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/jquery.form.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/painel.js?t=<?= time() ?>"></script>

        <script src="<?= BASE; ?>/Painel/_js/tinymce/tinymce.min.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/maskinput.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/highcharts.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/datepicker/datepicker.min.js"></script>
        <script src="<?= BASE; ?>/Painel/_js/datepicker/datepicker.pt-BR.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
        <script src="../painel.js"></script>
    </head>
    <body class="dashboard_main">
        <div class="workcontrol_upload workcontrol_loadmodal">
            <div class="workcontrol_upload_bar">
                <img class="m_botton" width="50" src="<?= BASE ?>/Painel/_img/load_w.gif" alt="Processando requisição!" title="Processando requisição!"/>
                <p><span class="workcontrol_upload_progrees">0%</span> - Processando requisição!</p>
            </div>
        </div>

        <?php
        echo $twig->render('menu.html', [
            "titulo" => "LivresBS",
            "menu_datas" => $calendario->listaDatas(),
            "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
            "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
        ]);

        if (isset($_SESSION['trigger_controll'])):
            ?>
            <script>
                $(function () {
                    var err = "<?= $_SESSION['trigger_controll'] ?>";
                    Trigger(err);
                });
            </script>
            <?php
            unset($_SESSION['trigger_controll']);
        endif;
        ?>

        <div class="dashboard">
            <?php
//QUERY STRING
            if (!empty($getView)):
                $includepatch = strip_tags(trim($getView)) . '.php';
            else:
                $includepatch = './Painel/montagem/index.php';
            endif;

            if (file_exists($includepatch)):
                require_once($includepatch);
            else:
                $_SESSION['trigger_controll'] = "<b>OPPSSS:</b> <span class='fontred'>_sis/{$getView}.php</span> não existe ou ainda está em construção!";
                header('Location: ../');
                exit;
            endif;
            ?>
        </div>
        <script>
            $(function () {
                //DATEPICKER CONFIG
                var start = new Date(), prevDay, startHours = 0;

                // ÚLTIMO HORÁRIO
                start.setHours(0);
                start.setMinutes(0);

                var dataPicker = $('.jwc_datepicker_start').datepicker({
                    timepicker: true,
                    startDate: start,
                    minHours: startHours
                }).data('datepicker');
            });
        </script>
    </body>
</html>
<?php
ob_end_flush();
