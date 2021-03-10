(function ($) {
    "use strict";

    /*==================================================================
    [ Validate ]*/
    var input = $('.validate-input .input100');

    $('.validate-form').on('submit',function(){
        var check = true;

        for(var i=0; i<input.length; i++) {
            if(validate(input[i]) == false){
                showValidate(input[i]);
                check=false;
            }
        }

        return check;
    });


    $('.validate-form .input100').each(function(){
        $(this).focus(function(){
           hideValidate(this);
        });
    });

    function validate (input) {
        if($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
            if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) === null) {
                return false;
            }
        }
        else {
            if($(input).val().trim() === ''){
                return false;
            }
        }
    }

    function showValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).removeClass('alert-validate');
    }

    
    

})(jQuery);

//auto scroll do navbar

// Init Scrollspy
$('body').scrollspy({ target: '#main-nav' });

// Smooth Scrolling
$("#main-nav a").on('click', function (event) {
  if (this.hash !== "") {
    event.preventDefault();

    const hash = this.hash;

    $('html, body').animate({
      scrollTop: $(hash).offset().top
    }, 800, function () {

      window.location.hash = hash;
    });
  }
});

$(document).on('click','.navbar-collapse.in',function(e) {

    if( $(e.target).is('a') && ( $(e.target).attr('class') != 'navbar-nav' ) ) {
        $(this).collapse('hide');
    }

});

$(document).ready(function() {
    //http://www.formvalidator.net/
	$.validate({
		lang: 'pt',
		modules : 'brazil, security',
		form : '#contato',
		errorMessagePosition: 'inline'
	});
	$("#btnEnviar").click(function() {
	    $.ajax({
            method: "POST",
            url: "../API/home/index.php",
            data: {
                nome: $("#nome").val(),
                email: $("#email").val(),
                telefone: $("#telefone").val()
            }
        })
        .done(function() {
            $(".card-com-form").toggle();
            $(".card-sem-form").toggle();
        })
        .fail(function() {
            alert("Ops. Tivemos algum problema ao salvar seus dados. Por favor, tente novamente.");
        });
	});
});