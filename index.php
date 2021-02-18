<?php 

    /*
        Plugin Name: Login e Cadastro
        Description: Plugin de login e cadastro de usuários
        Version: 1.0.0
        Author: Bruno Lima
        Author URI:        https://author.example.com/
    
    */


    if(!function_exists('add_action')) {
        echo "O plugin não pode ser acessado diretamente";
        exit;
    }

    // Ativação do plugin
    function dl_ativacao_plugin() {
        // verifica a versão atual do wp e só ativa se a versão for superior a 4.5
        if(version_compare(get_bloginfo('version'), '4.5', '<')) {
            wp_die("Você precisa atualizar o wordpress para utilizar o plugin");
        }
    }

    register_activation_hook(__FILE__, 'dl_ativacao_plugin');


    // carregar css e js
    function carregar_js_css() {
        // admin_url() - Recupera o URL para a área administrativa do site atual.
        // home_url()  - Recupera o URL do site atual em que o front end está acessível.


        wp_enqueue_style('css', plugins_url('/style.css', __FILE__));
        wp_enqueue_script('js', plugins_url('/script.js', __FILE__), array('jquery'), '1.0', true);

        // transferir dados do php para o javascript
        wp_localize_script('js', 'login_obj', array(
            'ajax_url' => admin_url("admin-ajax.php"),
            'home_url' => home_url('/')
        ));
    }
    // gancho quando o plugin ou o tema é carregado
    add_action('wp_enqueue_scripts', 'carregar_js_css');

    
    
    // criação shortcode de login e cadastro
    function dl_auth_form_shortcode() {

        // file_get_contents() - pega o conteudo do arquivo temmplatelogin.php

        $formHTML = file_get_contents(plugins_url('login/templatelogin.php'));
 
        echo $formHTML;
    }
    add_shortcode('login_auth_form', 'dl_auth_form_shortcode');


    function criar_conta() {
        // empty() - verifica se o $_POST estiver vazio.
        // is_email() - Função WP - Verifica se um e-mail é válido.
        // username_exists() - Função WP - Verifica se existe algum username cadastrado no banco.
        // email_exists() - Função WP - Verifica se existe algum email cadastrado no banco
        // get_user_by()  - Função WP - pega um campo especifico desse usuário.
        // wp_set_current_user() - Função WP - Altera o usuário atual por ID ou nome.
        // wp_set_auth_cookie() - Função WP - Define os cookies de autenticação com base no ID do usuário. | O parâmetro $remember aumenta o tempo que o cookie será mantido. O padrão que o cookie é mantido sem lembrar é de dois dias.
        // do_action('wp_login') - Função WP - Dispara depois que o usuário faz login com sucesso.
        // wp_send_json() - Função WP - Envia uma resposta JSON de volta a uma solicitação Ajax.




        $array = array('status' => 1);
        
        if(empty($_POST['name']) || empty($_POST['email']) || empty($_POST['senha']) || !is_email($_POST['email'])) {
            wp_send_json($array);
        }


        // TRATANDO OS DADOS
        // Sanatização = proteção que o wordpress faz quando recebe um post de um determinado formulario.
        $name  = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $senha = sanitize_text_field($_POST['senha']);


        // PEGANDO O NOME DO USUARIO ANTES DO @ email

        $username = explode('@', $email);
        $username = $username[0];

        
        // Verifica se existe username e email na base de dados.
        if(username_exists($username) || email_exists($email)) {
            wp_send_json($array);
        }

        // Insere o usuario na base de dados.
        $user_id = wp_insert_user(array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $senha,
            'user_nicename' => $username
        ));


        // Verifica se foi insrido o usuario mas não retornou o ID
        if(is_wp_error($user_id)) {
            wp_send_json($array);
        }


        // Fluxo foi perfeito? Redireciona para a home
        $user = get_user_by('id', $user_id);

        wp_set_current_user($user_id,$user->user_login);

        // ao redirecionar o usuario com o js é criado um coockie para armazenar os dados do usuario no navegador.
        wp_set_auth_cookie($user_id, false); // false - garante que ele não continuará logado. apos o tempo sera deslogado automatico.

        do_action('wp_login', $user->user_login, $user); // Dispara depois que o usuário faz login com sucesso

        $array['status'] = 2;

        wp_send_json($array); // se retornou 2 é retornado no response do js

    }

    add_action('wp_ajax_nopriv_criar_conta', 'criar_conta');

    function login() {
        // wp_signon() - Autentica e registra um usuário com o recurso de 'lembrar'.

        $array = array('status' => 1);
        
        if(empty($_POST['email']) || empty($_POST['senha']) || !is_email($_POST['email'])) {
            wp_send_json($array);
        }

        $email = sanitize_email($_POST['email']);
        $senha = sanitize_text_field($_POST['senha']);

        
        // Verifica se NÃO existe email na base de dados.
        if(!email_exists($email)) {
            wp_send_json($array); // retorna status 1 erro.
        }

        $userdata = get_user_by('email',$email);

        $user = wp_signon(array(
            'user_login'     => $userdata->user_login,
            'user_password'  => $senha,
            'remember'       => true // quero que o user fique logado quando ele fechar o navegador.
        ));

        if(is_wp_error($user)) {
            wp_send_json($array);
        }

        $array['status'] = 2;

        wp_send_json($array); // se retornou 2 é retornado no response do js    
    }
    
    add_action('wp_ajax_nopriv_login', 'login');