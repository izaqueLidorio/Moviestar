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

//atualiza usuario
if ($type === "update") {

    //resgata dados do usuario
    $userData = $userDao->verifyToken();

    //recebe dados do post
    $name = filter_input(INPUT_POST, "name");
    $lastname = filter_input(INPUT_POST, "lastname");
    $email = filter_input(INPUT_POST, "email");
    $bio = filter_input(INPUT_POST, "bio");

    //cria um novo objeto do usuario
    $user = new User();

    // preencher os dados de usuario
    $userData->name = $name;
    $userData->lastname = $lastname;
    $userData->email = $email;
    $userData->bio = $bio;

    //Upload da imagem
    if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

        $image = $_FILES["image"];
        $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
        $jpgarray = ["image/jpeg", "image/jpg"];

        //checagen de tipo de imagem
        if (in_array($image["type"], $imageTypes)) {

            //checar se é jpg
            // Checa se é jpg
            if (in_array($image["type"], ["image/jpeg", "image/jpg"])) {
                $imageFile = imagecreatefromjpeg($image["tmp_name"]);
            } else {
                $imageFile = imagecreatefrompng($image["tmp_name"]);
            }

            $imageName = $user->imageGenereteName();

            imagejpeg($imageFile, "./img/users/" . $imageName, 100);

            $userData->image = $imageName;

        } else {
            $message->setMessage("Tipo de imagem invalida", "error", "back");
        }

    }
    $userDao->update($userData);



    // atualizar a senha do usuario
} elseif ($type === "changepassword") {

    $password = filter_input(INPUT_POST, "password");
    $confirmpassword = filter_input(INPUT_POST, "confirmpassword");

    //resgata dados do usuario
    $userData = $userDao->verifyToken();
    $id = $userData->id;

    if ($password == $confirmpassword) {

        //cria um novo objeto do usuario
        $user = new User();

        $finalPassword = $user->generatePassword($password);

        $user->password = $finalPassword;
        $user->id = $id;
        $userDao->changePassword($user);


    } else {

        $message->setMessage("As senhas devem ser iguais", "error", "back");
    }

} else {

    $message->setMessage("Informações invalidas", "error", "index.php");
}