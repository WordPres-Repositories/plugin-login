jQuery('#cadastro').on('submit', function(e) {
    e.preventDefault();

    var $ = jQuery;

    $('.messagem_cadastro').html('Carregando...');
    $('#botao_cadastro').hide();
    
    // PEGA OS DADOS DO FORMULARIO CADASTRO
    var form = {
        action: 'criar_conta', // manda o retorno do ajax para essa action la no arquivo index.php
        name:  $('#cadastro_name').val(),
        email: $('#cadastro_email').val(),
        senha: $('#cadastro_senha').val(),
    }
    
    // VAI PROCESSAR O AJAX VIA POST MANDANDO PRA URL ADMIN-AJAX.PHP
    $.ajax({
        type: 'POST',
        dataType: 'json', // se não tiver o tipo o ajax não funciona
        url: login_obj.ajax_url, // alert(login_obj.ajax_url); - retorna o ajax do wordpress http://projeto/wp-admin/admin-ajax.php
        data: form,
        success: function (json) {

            if(json.status == 2) {
                $(".messagem_cadastro").html("Cadastrado com sucesso!");
                window.location.href = login_obj.home_url;
            } else {
                $(".messagem_cadastro").html("Erro ao cadastrar o usuario");
            }
        }
    });

});


// LOGIN
jQuery('#login').on('submit', function(e) {
    e.preventDefault();

    var $ = jQuery;

    $('.messagem_login').html('Carregando...');
    $('#botao_login').hide();
    
    // PEGA OS DADOS DO FORMULARIO CADASTRO
    var form = {
        action: 'login', // manda o retorno do ajax para essa action la no arquivo index.php
        email: $('#login_email').val(),
        senha: $('#login_senha').val(),
    }
    
    // VAI PROCESSAR O AJAX VIA POST MANDANDO PRA URL ADMIN-AJAX.PHP
    $.ajax({
        type: 'POST',
        dataType: 'json', // se não tiver o tipo o ajax não funciona
        url: login_obj.ajax_url, // alert(login_obj.ajax_url); - retorna o ajax do wordpress http://projeto/wp-admin/admin-ajax.php
        data: form,
        success: function (json) {

            if(json.status == 2) {
                $(".messagem_login").html("Logado com sucesso!");
                window.location.href = login_obj.home_url;
            } else {
                $(".messagem_login").html("Erro ao logar.");
            }
        }
    });

});