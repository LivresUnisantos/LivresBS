$(document).ready(function() {
    function obtemDataSessao() {
        /*var loc = window.location.pathname;
        last = loc.split('/')[2];
        file = last.substring(last.length-4);
        
        if (file == '.php' || file == 'html') {
            var url = 'defineDataSessao.php';
        } else {
            var url = '../defineDataSessao.php';
        }*/
        url = '../../../Painel/defineDataSessao.php';
        return Promise.resolve($.ajax({
            // method: "GET",
            url: url,
        }));
    }

    $("#navbarSupportedContent a").click(function(eventObject) {
        eventObject.preventDefault();
        endereco = $(this).attr('href');
        if ($(this).attr('condicionalData') == 'sim') {
            if ($("#data_selecao_menu_sup").val() === '') {
                alert('Selecione uma data e pressione o botão para prosseguir para esta página');
                return;                    
            } else {
                var promise = obtemDataSessao();
                promise.then(data => {
                    if (data === "") {
                        alert('Selecione uma data e pressione o botão para prosseguir para esta página');
                        $("#data_selecao_menu_sup").val("");
                    } else {
                        window.location = endereco;
                    }
                });
            }
        } else {
            window.location = endereco;
        }
    });
    $("#menuData").one('submit', function(eventObject) {
        eventObject.preventDefault();
        let data = $("#data_selecao_menu_sup").val();
        $.ajax({
                method: "GET",
                url: '../../../Painel/defineDataSessao.php',
                data: {
                    data: data                    
                }
            })
        .done(function(msg) {
            console.log(1,msg);
            //$(this).attr('action', window.location.search);
            //$(this).submit();
            if (window.location.search !== '') {
                var url = new URL(window.location.href);
                var search_params = url.searchParams;
                
                search_params.set('data',data);
                window.location = url.search;
            } else {
                window.location = '?data=' + data;
            }
        });
        //$(this).submit();
        //return;
    });
});