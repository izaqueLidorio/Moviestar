<?php

require_once('globals.php');
require_once('db.php');
require_once('models/User.php');
require_once('models/Message.php');
require_once('dao/UserDAO.php');

$message = new Message($BASE_URL);

$userDao = new UserDao($conn, $BASE_URL);

//Resgata o tipo de formulario
$type = filter_input(INPUT_POST, 'type');

//verificação do tipo de formulario
if ($type === 'register') {

  $name = filter_input(INPUT_POST, 'name');
  $lastname = filter_input(INPUT_POST, 'lastname');
  $email = filter_input(INPUT_POST, 'email');
  $password = filter_input(INPUT_POST, 'password');
  $confirmpassword = filter_input(INPUT_POST, 'confirmpassword');

  //verificação de dados minimos
  if ($name && $lastname && $email && $password) {

    //verifica se as senhas batem
    if ($password === $confirmpassword) {

      //verifica se o email já esta cadastrado no sistema
      if ($userDao->findByEmail($email) === false) {

        $user = new User();

        //CRIAÇÃO DE TOKEN E SENHA  
        $userToken = $user->generateToken();
        $finalPassword = $user->generatePassword($password);
        $user->name = $name;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->password = $finalPassword;
        $user->token = $userToken;
        $auth = true;
        $userDao->create($user, $auth);

      } else {
        // Enviar uma mensagen de erro, AS senha não são iguais
        $message->setMessage("Usuario já cadastrado, tente outro email", "error", "back");
      }
    } else {
      // Enviar uma mensagen de erro, AS senha não são iguais
      $message->setMessage("As senhas não são iguais.", "error", "back");
    }

  } else {
    // Enviar uma mensagen de erro por falta de dados
    $message->setMessage("Por favor, preencha todos os campos.", "error", "back");
  }



} else if ($type === "login") {

  $email = filter_input(INPUT_POST, 'email');
  $password = filter_input(INPUT_POST, 'password');

  //tenta autenticar um usuario
  if ($userDao->authenticateUser($email, $password)) {

    $message->setMessage("Seja bem-vindo!!", "success", redirect: "editprofile.php");


    //redireciona o usuario,  caso não conseguir autenticar
  } else {
    $message->setMessage("Usuario ou senha incorretos.", "error", "back");
  }

} else {
  $message->setMessage("Informações invalidas.", "error", "index.php");
}