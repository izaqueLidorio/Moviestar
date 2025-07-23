<?php

require_once('globals.php');
require_once('db.php');
require_once('models/Movie.php');
require_once('models/Message.php');
require_once('dao/UserDAO.php');
require_once('dao/MovieDAO.php');

//Resgata o tipo de formulario
$type = filter_input(INPUT_POST, 'type');

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$movieDao = new MovieDAO($conn, $BASE_URL);


//resgata dados do usuario
$userData = $userDao->verifyToken();

// Função para converter a URL do YouTube para embed
function convertYouTubeUrl($url)
{
    if (strpos($url, "watch?v=") !== false) {
        return str_replace("watch?v=", "embed/", $url);
    }
    return $url; // Se não for um link do YouTube no formato errado, mantém o original
}


if ($type === "create") {

    //receber os dados dos inputs
    $title = filter_input(INPUT_POST, "title");
    $description = filter_input(INPUT_POST, "description");
    $trailer = filter_input(INPUT_POST, "trailer");
    $category = filter_input(INPUT_POST, "category");
    $length = filter_input(INPUT_POST, "length");
    // Converte a URL antes de salvar
    $trailerEmbed = convertYouTubeUrl($trailer);

    $movie = new Movie();

    //verificação minima de dados
    if (!empty($title) && !empty($description) && !empty($category)) {

        $movie->title = $title;
        $movie->description = $description;
        $movie->trailer = $trailerEmbed;
        $movie->category = $category;
        $movie->length = $length;
        $movie->users_id = $userData->id;

        //upload de imagem do filme

        // Upload de imagem
        if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

            $image = $_FILES["image"];

            // Checando tipo da imagem
            if (in_array($image["type"], ["image/jpeg", "image/jpg", "image/png"])) {

                // Checa se é jpg
                if (in_array($image["type"], ["image/jpeg", "image/jpg"])) {
                    $imageFile = imagecreatefromjpeg($image["tmp_name"]);
                } else {
                    $imageFile = imagecreatefrompng($image["tmp_name"]);
                }

                $imageName = $movie->imageGenerateName();
                imagejpeg($imageFile, "./img/movies/" . $imageName, 100);
                $movie->image = $imageName;

            } else {
                $message->setMessage("Tipo inválido de imagem, envie jpg ou png!", "error", "editprofile.php");
            }
        }


        $movieDao->create($movie);



    } else {
        $message->setMessage("Adicione informações ao filme", "error", "back");
    }


    //deletar fime 
} else if ($type === "delete") {
    //recebe os dados do form
    $id = filter_input(INPUT_POST, "id");
    $movie = $movieDao->findById($id);

    if ($movie) {
        //verifica se o filme é do usuario
        if ($movie->users_id === $userData->id) {

            $movieDao->destroy($movie->id);

        } else {
            $message->setMessage("Informações invalidas", "error", "index.php");
        }

    }



    // Editar filme
} else if ($type === "update") {

    // Receber os dados dos inputs
    $title = filter_input(INPUT_POST, "title");
    $description = filter_input(INPUT_POST, "description");
    $trailer = filter_input(INPUT_POST, "trailer");
    $category = filter_input(INPUT_POST, "category");
    $length = filter_input(INPUT_POST, "length");
    $id = filter_input(INPUT_POST, "id");

    // Converte a URL antes de salvar
    $trailerEmbed = convertYouTubeUrl($trailer);

    // Busca o filme no banco de dados
    $movieData = $movieDao->findById($id);

    // Verifica se o filme foi encontrado
    if ($movieData) {

        // Verifica se o filme pertence ao usuário logado
        if ($movieData->users_id === $userData->id) {

            // Verificação mínima de dados
            if (!empty($title) && !empty($description) && !empty($category)) {

                // Atualiza os dados do filme
                $movieData->title = $title;
                $movieData->description = $description;
                $movieData->trailer = $trailerEmbed;
                $movieData->category = $category;
                $movieData->length = $length;

                // Upload de imagem (se enviada)
                if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

                    $image = $_FILES["image"];

                    // Verifica o tipo da imagem
                    if (in_array($image["type"], ["image/jpeg", "image/jpg", "image/png"])) {

                        // Gerando nome único para a imagem
                        $movie = new Movie();
                        $imageName = $movie->imageGenerateName();

                        // Verifica o formato e cria a imagem
                        if (in_array($image["type"], ["image/jpeg", "image/jpg"])) {
                            $imageFile = imagecreatefromjpeg($image["tmp_name"]);
                        } else {
                            $imageFile = imagecreatefrompng($image["tmp_name"]);
                        }

                        // Salva a imagem no diretório
                        imagejpeg($imageFile, "./img/movies/" . $imageName, 100);
                        imagedestroy($imageFile); // Libera memória

                        // Atualiza a imagem no banco de dados
                        $movieData->image = $imageName;

                    } else {
                        $message->setMessage("Tipo inválido de imagem, envie jpg ou png!", "error", "editprofile.php");
                    }
                }

                // Atualizar o filme no banco de dados
                $movieDao->update($movieData);

                // Mensagem de sucesso
                $message->setMessage("Filme atualizado com sucesso!", "success", "dashboard.php");

            } else {
                $message->setMessage("Adicione informações ao filme", "error", "back");
            }

        } else {
            $message->setMessage("Informações inválidas", "error", "index.php");
        }

    } else {
        $message->setMessage("Informações inválidas", "error", "index.php");
    }
}