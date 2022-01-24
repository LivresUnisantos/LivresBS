$(function () {
    BASE = $("link[rel='base']").attr("href");

    //WC LOAD MODAL
    $('.jwc_load_modal').click(function () {
        $('.workcontrol_upload').fadeIn().css('display', 'flex');
    });

    //WC TAB
    $('.wc_tab').click(function () {
        if (!$(this).hasClass('wc_active')) {
            var WcTab = $(this).attr('href');
            $('.wc_tab').removeClass('wc_active');
            $(this).addClass('wc_active');
            $('.wc_tab_target.wc_active').fadeOut(200, function () {
                $(WcTab).fadeIn(300).addClass('wc_active');
            }).removeClass('wc_active');
        }

        if (!$(this).hasClass('wc_active_go')) {
            return false;
        }
    });

    //WC TAB AUTOCLICK
    if (window.location.hash) {
        $("a[href='" + window.location.hash + "']").click();
        setTimeout(function () {
            $(".jwc_open_" + wcUrlParam('open')).click();
        }, 100);
    }

    //IMAGE ERROR
    $('img').error(function () {
        var s, w, h;
        s = $(this).attr('src');
        $(this).attr('src', BASE + '/_img/no_image.jpg');
    });

    //NEW LINE ACTION
    $('textarea').keypress(function (event) {
        if (event.which === 13) {
            var s = $(this).val();
            $(this).val(s + "\n");
        }
    });

    //AUTOSAVE ACTION
    $('html').on('change', 'form.auto_save', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var form = $(this);
        
        var callback = form.find('input[name="callback"]').val();
        var callback_action = form.find('input[name="callback_action"]').val();
        
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }

        form.ajaxSubmit({
            url: BASE + '/_ajax/' + callback + '.ajax.php',
            data: {callback_action: callback_action},
            dataType: 'json',
            beforeSubmit: function () {
                form.find('.form_load').fadeIn('fast');
            },
            uploadProgress: function (evento, posicao, total, completo) {
                var porcento = completo + '%';
                $('.workcontrol_upload_progrees').text(porcento);
                if (completo <= '80') {
                    $('.workcontrol_upload').fadeIn().css('display', 'flex');
                }
                if (completo >= '99') {
                    $('.workcontrol_upload').fadeOut('slow', function () {
                        $('.workcontrol_upload_progrees').text('0%');
                    });
                }
                //PREVENT TO RESUBMIT IMAGES GALLERY
                form.find('input[name="image[]"]').replaceWith($('input[name="image[]"]').clone());
            },

            success: function (data) {
                form.find('.form_load').fadeOut('slow', function () {

                    if (data.name) {
                        var input = form.find('.wc_name');
                        if (!input.val() || input.val() != data.name) {
                            input.val(data.name);
                        }

                        var inputfield = form.find('input[name*=_name]');
                        if (inputfield) {
                            inputfield.val(data.name);
                        }
                    }

                    if (data.gallery) {
                        form.find('.gallery').fadeTo('300', '0.5', function () {
                            $(this).append(data.gallery);
                        }).fadeTo('300', '1');
                    }

                    if (data.view) {
                        $('.wc_view').attr('href', data.view);
                    }

                    if (data.reorder) {
                        $('.wc_drag_active').removeClass('btn_yellow');
                        $('.wc_draganddrop').removeAttr('draggable');
                    }

                    //DATA CONTENT IN j_content
                    if (data.content) {
                        if (typeof (data.content) === 'string') {
                            $('.j_content').fadeTo('300', '0.5', function () {
                                $(this).html(data.content).fadeTo('300', '1');
                            });
                        } else if (typeof (data.content) === 'object') {
                            $.each(data.content, function (key, value) {
                                $(key).fadeTo('300', '0.5', function () {
                                    $(this).html(value).fadeTo('300', '1');
                                });
                            });
                        }
                    }

                    if (data.montagem) {
                        $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                        $('.workcontrol_upload').fadeIn().css('display', 'flex');
                        window.setTimeout(function () {
                            if (typeof (data.montagem) === 'string') {
                                $('.j_montagem').fadeTo('300', '0.5', function () {
                                    $(this).html(data.montagem).fadeTo('300', '1');
                                });
                            } else if (typeof (data.montagem) === 'object') {
                                $.each(data.montagem, function (key, value) {
                                    $(key).fadeTo('300', '0.5', function () {
                                        $(this).html(value).fadeTo('300', '1');
                                    });
                                });
                            }
                        }, 500);
                        $('.workcontrol_upload').fadeOut();
                    }

                    //DATA DINAMIC CONTENT
                    if (data.divcontent) {
                        $.each(data.divcontent, function (key, value) {
                            $(key).html(value);
                        });
                    }

                    //DATA DINAMIC CONTENT
                    if (data.retirada) {
                        $(data.retirada[0]).html(data.retirada[1]);
                    }

                    //DATA CLEAR
                    if (data.clear) {
                        form.trigger('reset');
                        if (form.find('.label_publish')) {
                            form.find('.label_publish').removeClass('active');
                        }
                        $('.js-example-basic-single').select2("val", "");
                    }

                    //CLEAR INPUT FILE
                    if (!data.error) {
                        form.find('input[type="file"]').val('');
                    }

                    if (data.desativarBtn) {
                        form.find('.item_verificado_' + data.desativarBtn).text('Verificar').removeClass('active');
                        form.find('#item_verificado_' + data.desativarBtn).val(1);
                    }

                    if (data.itemValor) {
                        form.find('.j_itemValor').text(data.itemValor);
                    }

                    if (data.redirect) {
                        $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                        $('.workcontrol_upload').fadeIn().css('display', 'flex');
                        window.setTimeout(function () {
                            window.location.href = data.redirect;
                            if (window.location.hash) {
                                window.location.reload();
                            }
                        }, 1500);
                    }

                    if (data.trigger) {
                        Trigger(data.trigger);
                    }
                });
            }
        });
    });

    //Coloca todos os formulários em AJAX mode e inicia LOAD ao submeter!
    $('html').on('submit', 'form:not(.ajax_off)', function () {

        var form = $(this);
        var callback = form.find('input[name="callback"]').val();
        var callback_action = form.find('input[name="callback_action"]').val();
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }

        form.ajaxSubmit({
            url: BASE + '/_ajax/' + callback + '.ajax.php',
            data: {callback_action: callback_action},
            dataType: 'json',
            beforeSubmit: function () {
                form.find('.form_load').fadeIn('fast');
            },
            uploadProgress: function (evento, posicao, total, completo) {
                var porcento = completo + '%';
                $('.workcontrol_upload_progrees').text(porcento);
                if (completo <= '80') {
                    $('.workcontrol_upload').fadeIn().css('display', 'flex');
                }
                if (completo >= '99') {
                    $('.workcontrol_upload').fadeOut('slow', function () {
                        $('.workcontrol_upload_progrees').text('0%');
                    });
                }
                //PREVENT TO RESUBMIT IMAGES GALLERY
                form.find('input[name="image[]"]').replaceWith($('input[name="image[]"]').clone());
            },
            success: function (data) {
                //REMOVE LOAD
                form.find('.form_load').fadeOut('slow', function () {

                    if (data.itemId) {
                        form.find("span.j_del_item").remove();
                        form.append("<input type='hidden' name='item_id' value='" + data.itemId + "' />");
                        form.find('.j_remove_item').attr('id', data.itemId);
                        var excluirBtn = form.find('.j_action');
                        excluirBtn.append("<span rel='j_remove_item' class='j_delete_action icon-cancel-circle btn btn_red' id='" + data.itemId + "'>Excluir</span><span rel='j_remove_item' callback='Pedidos' callback_action='deleteItem' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='" + data.itemId + "'>Remover Item?</span>");
                    }

                    //REDIRECIONA
                    if (data.redirect) {
                        $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                        $('.workcontrol_upload').fadeIn().css('display', 'flex');
                        window.setTimeout(function () {
                            window.location.href = data.redirect;
                            if (window.location.hash) {
                                window.location.reload();
                            }
                        }, 1500);
                    }

                    //INTERAGE COM TINYMCE
                    if (data.tinyMCE) {
                        tinyMCE.activeEditor.insertContent(data.tinyMCE);
                        $('.workcontrol_imageupload').fadeOut('slow', function () {
                            $('.workcontrol_imageupload .image_default').attr('src', BASE + '/_img/no_image.jpg');
                        });
                    }

                    //GALLETY UPDATE HTML
                    if (data.gallery) {
                        form.find('.gallery').fadeTo('300', '0.5', function () {
                            $(this).append(data.gallery).fadeTo('300', '1');
                        });
                    }

                    //DATA CONTENT IN j_content
                    if (data.content) {
                        if (typeof (data.content) === 'string') {
                            $('.j_content').fadeTo('300', '0.5', function () {
                                $(this).html(data.content).fadeTo('300', '1');
                            });
                        } else if (typeof (data.content) === 'object') {
                            $.each(data.content, function (key, value) {
                                $(key).fadeTo('300', '0.5', function () {
                                    $(this).html(value).fadeTo('300', '1');
                                });
                            });
                        }
                    }

                    //DATA DINAMIC CONTENT
                    if (data.divcontent) {
                        if (typeof (data.divcontent) === 'string') {
                            $(data.divcontent[0]).html(data.divcontent[1]);
                        } else if (typeof (data.divcontent) === 'object') {
                            $.each(data.divcontent, function (key, value) {
                                $(key).html(value);
                            });
                        }
                    }

                    //DATA DINAMIC FADEOUT
                    if (data.divremove) {
                        if (typeof (data.divremove) === 'string') {
                            $(data.divremove).fadeOut();
                        } else if (typeof (data.divremove) === 'object') {
                            $.each(data.divremove, function (key, value) {
                                $(value).fadeOut();
                            });
                        }
                    }

                    //DATA CLICK
                    if (data.forceclick) {
                        if (typeof (data.forceclick) === 'string') {
                            setTimeout(function () {
                                $(data.forceclick).click();
                            }, 250);
                        } else if (typeof (data.forceclick) === 'object') {
                            $.each(data.forceclick, function (key, value) {
                                setTimeout(function () {
                                    $(value).click();
                                }, 250);
                            });
                        }
                    }

                    //DATA DOWNLOAD IN j_downloa
                    if (data.download) {
                        $('.j_download').fadeTo('300', '0.5', function () {
                            $(this).html(data.download).fadeTo('300', '1');
                        });
                    }

                    //DATA HREF VIEW
                    if (data.view) {
                        $('.wc_view').attr('href', data.view);
                    }

                    //DATA REORDER
                    if (data.reorder) {
                        $('.wc_drag_active').removeClass('btn_yellow');
                        $('.wc_draganddrop').removeAttr('draggable');
                    }

                    //DATA CLEAR
                    if (data.clear) {
                        form.trigger('reset');
                        if (form.find('.label_publish')) {
                            form.find('.label_publish').removeClass('active');
                        }
                    }

                    //DATA CLEAR INPUT
                    if (data.inpuval) {
                        if (data.inpuval === 'null') {
                            $('.wc_value').val("");
                        } else {
                            $('.wc_value').val(data.inpuval);
                        }
                    }

                    //CLEAR INPUT FILE
                    if (!data.error) {
                        form.find('input[type="file"]').val('');
                    }

                    if (data.trigger) {
                        Trigger(data.trigger);
                    }
                });
            }
        });
        return false;
    });

    //WC COMBO BOX
    $('.jwc_combo').change(function () {
        var callback = $(this).attr('data-c');
        var callback_action = $(this).attr('data-ca');
        var key = $(this).find('option').filter(":selected").val();
        $.post(BASE + '/_ajax/' + callback + '.ajax.php', {callback: callback, callback_action: callback_action, key: key}, function (data) {
            if (data.target) {
                $(data.target).html(data.content);
            }
        }, 'json');
    });

//    //Ocultra Trigger clicada
//    $('html').on('click', '.trigger_ajax, .trigger_modal', function () {
//        $(this).fadeOut('slow', function () {
//            $(this).remove();
//        });
//    });

    //Publish Effect
    $('.label_publish').click(function () {
        if (!$(this).find('input').is(':checked')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });


    //############# GERAIS
    //DELETE CONFIRM
    $('html, body').on('click', '.j_delete_action', function (e) {
        var RelTo = $(this).attr('rel');
        $(this).fadeOut(10, function () {
            $('.' + RelTo + '[id="' + $(this).attr('id') + '"] .j_delete_action_confirm:eq(0)').fadeIn(10);
        });
        e.preventDefault();
        e.stopPropagation();
    });

    //DELETE CONFIRM ACTION
    $('html, body').on('click', '.j_delete_action_confirm', function (e) {
        var Prevent = $(this);
        var DelId = $(this).attr('id');
        var RelTo = $(this).attr('rel');
        var Callback = $(this).attr('callback');
        var Callback_action = $(this).attr('callback_action');
        $('.workcontrol_upload p').html("Processando requisição, aguarde!");
        $('.workcontrol_upload').fadeIn().css('display', 'flex');
        $.post(BASE + '/_ajax/' + Callback + '.ajax.php', {callback: Callback, callback_action: Callback_action, del_id: DelId}, function (data) {
            if (data.trigger) {
                Trigger(data.trigger);
                $('.' + RelTo + '[id="' + Prevent.attr('id') + '"] .j_delete_action_confirm:eq(0)').fadeOut('fast', function () {
                    $('.' + RelTo + '[id="' + Prevent.attr('id') + '"] .j_delete_action:eq(0)').fadeIn('fast');
                });
            } else {
                $('.' + RelTo + '[id="' + DelId + '"]').fadeOut('fast');
            }

            if (data.recado) {
                Trigger(data.recado);
            }

            //REDIRECIONA
            if (data.redirect) {
                $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                $('.workcontrol_upload').fadeIn().css('display', 'flex');
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                    if (window.location.hash) {
                        window.location.reload();
                    }
                }, 1500);
            } else {
                $('.workcontrol_upload').fadeOut();
            }

            //CONTENT UPDATE
            if (data.content) {
                $('.j_content').fadeTo('300', '0.5', function () {
                    $(this).html(data.content).fadeTo('300', '1');
                    HeaderRender('wc_normalize_height');
                });
            }

            //INPUT CLEAR
            if (data.inpuval) {
                if (data.inpuval === 'null') {
                    $('.wc_value').val("");
                } else {
                    $('.wc_value').val(data.inpuval);
                }
            }

            //DINAMIC CONTENT
            if (data.divcontent) {
                $(data.divcontent[0]).html(data.divcontent[1]);
            }
        }, 'json');
        e.preventDefault();
        e.stopPropagation();
    });


    //############## SOCIAL SHARE
    function HeaderRender(Class) {
        var maxHeight = 0;
        $("." + Class + ":visible").each(function () {
            if ($(this).height() > maxHeight) {
                maxHeight = $(this).height();
            }
        }).height(maxHeight);
    }

    $(window).load(function () {
        HeaderRender('wc_normalize_height');
    });


    //MARCAR SEPARADO | VERIFICADO
    $('html').on('click', '.label_yn', function () {
        var labelName = $(this).attr('for');
        var campo = labelName.substring(6, 5);

        if (campo == 's') {
            if ($(this).hasClass('active')) {
                $('.' + labelName + '').removeClass('active');
                $('.' + labelName + '').text('Separar');
                $('#' + labelName + '').val('1');
            } else {
                $('#' + labelName + '').val('2');
                $(this).text('Separado');
            }            
        } else if (campo == 'v') {
            if ($('#' + labelName + '').val() == 1) {
                $(this).addClass('active');
                $('.' + labelName + '').text('Verificado'); 
                $('#' + labelName + '').val('2');                
            } else if ($('#' + labelName + '').val() == 2) {
                $(this).addClass('activeVerify');
                $('.' + labelName + '').removeClass('active');            
                $('.' + labelName + '').text('Verificado 2x');
                $('#' + labelName + '').val('3');
            } else if ($('#' + labelName + '').val() == 3) {
                $('.' + labelName + '').removeClass('activeVerify');
                $('.' + labelName + '').text('Verificar');
                $('#' + labelName + '').val('1');
            }
        }
        $('#' + labelName + '').change();
    });

    $('.panel').on('click', '.j_marcar_prod', function () {
        $('.j_item_sel').prop("checked", true);
    });

    $('.j_desmarcar_prod').on('click', function () {
        $('.j_item_sel').prop("checked", false);
    });

//    j_valor
    $('html, body').on('change', '.j_produto', function (e) {
        var ProdutoVal = $(this).val();
        var Valor = $(this).parents(':eq(1)').find("select[name='item_valor']");
        var Callback = "Pedidos";
        var Callback_action = "ProdutoValor";
        $.post(BASE + '/_ajax/Pedidos.ajax.php', {callback: Callback, callback_action: Callback_action, id: ProdutoVal}, function (data) {
            if (data.options) {
                Valor.children('option').remove();
                Valor.append(data.options);
            }
        }, 'json');
        e.preventDefault();
        e.stopPropagation();
    });

    //EFEITO SANFONA
    $('.j_sanfona_todos').on('click', function () {
        $('.j_sanfona_desc').stop().slideToggle(600);
        if ($(this).hasClass('icon-shrink')) {
            $(this).removeClass('icon-shrink');
            $(this).addClass('icon-enlarge').html("<span class='wc_tooltip_balloon'>Expandir todos</span>");
        } else {
            $(this).addClass('icon-shrink').html("<span class='wc_tooltip_balloon'>Recolher todos</span>");
            $(this).removeClass('icon-enlarge');
        }
    });

    //EFEITO SANFONA INVIDUAL
    $('.j_sanfona').on('click', function () {
        $(this).parents('.single_order').find('.j_sanfona_desc').stop().slideToggle(300, function () {
            if ($(this).parents('.single_order').find('.j_sanfona').hasClass('icon-shrink2')) {
                $(this).parents('.single_order').find('.j_sanfona').removeClass('icon-shrink2').addClass('icon-enlarge2').html("<span class='wc_tooltip_balloon'>Expandir</span>");
                ;
            } else {
                $(this).parents('.single_order').find('.j_sanfona').removeClass('icon-enlarge2').addClass('icon-shrink2').html("<span class='wc_tooltip_balloon'>Recolher</span>");
                ;
            }
        });
    });

    $("body").on('click', '.trigger_notify', function () {
        $(this).animate({"left": "100%", "opacity": "0"}, 200, function () {
            $(this).remove();
        });
    });

});

function wcUrlParam(name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    return (results ? results[1] : null);
}

//############## MODAL MESSAGE
function Trigger(Message) {
    var timeMessage = 5000;
    var triggerMessage = "<div class='trigger_notify' style='left: 100%; opacity: 0;'>";
    triggerMessage += Message;
    triggerMessage += "<span class='trigger_notify_time'></span>";
    triggerMessage += "</div>";
    if (!$(".trigger_notify_box").length) {
        $("body").prepend("<div class='trigger_notify_box'></div>");
    } else {
        $(".trigger_notify:gt(1)").animate({"left": "100%", "opacity": "0"}, 400, function () {
            $(this).remove();
        });
    }
    $(".trigger_notify_box").prepend(triggerMessage);
    $(".trigger_notify:first").stop().animate({"left": "0", "opacity": "1"}, 200, function () {
        $(this).find(".trigger_notify_time").animate({"width": "100%"}, timeMessage, "linear", function () {
            $(this).parent(".trigger_notify").animate({"left": "100%", "opacity": "1"}, 200, function () {
                $(this).remove();
            });
        });
    });

}
