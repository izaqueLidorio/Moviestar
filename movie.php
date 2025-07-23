<?php

require_once("templates/header.php");

require_once("models/Movie.php");
require_once("dao/MovieDAO.php");
require_once("dao/ReviewDAO.php");


//pegar o id do fime para exibirlo
$id = filter_input(INPUT_GET, "id");

$movie;
$movieDao = new MovieDao($conn, $BASE_URL);
$reviewDao = new ReviewDao($conn, $BASE_URL);



if (empty($id)) {
    //se o id estiver vazio redireciona para o index
    $message->setMessage("O filme não foi encontrado!", "error", "index.php");

} else {

    $movie = $movieDao->findById($id);

    //Verifica se o filme existe
    if (!$movie) {
        $message->setMessage("O filme não foi encontrado!", "error", "index.php");

    }
}
// checa se o filme tem imagem
if ($movie->image == "") {
    $movie->image = "movie_cover.jpg";
}
// Checar se o filme é do usuario // se for devemos impedi-lo de fazer review
$userOwnsMovie = false;

if (!empty($userData)) {
    if ($userData->id === $movie->users_id) {
        $userOwnsMovie = true;
    }

    //Resgata as reviews do filme
   $alreadyReviewed = $reviewDao->hasAlreadyReviewed($id, $userData->id);
}


// Resgatar as reviews do filme 
$moviesReviews = $reviewDao->getMoviesReview($id);


?>


<div id="main-container" class="container-fluid">
    <div class="row">
        <div class="offset-md-1 col-md-6 movie-container">
            <h1 class="page-title"> <?= $movie->title ?> </h1>
            <p class="movie-details">
                <span>Duração: <?= $movie->length ?> </span>
                <span class="pipe"></span>
                <span>Categoria: <?= $movie->category ?> </span>
                <span class="pipe"></span>
                <span><i class="fas fa-star"><?= $movie->rating?></i></span>
            </p>
            <?php if (!empty($movie->trailer)): ?>
            <iframe width="560" height="315" src="<?= ($movie->trailer) ?>" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
            <?php else: ?>
            <p>Nenhum trailer disponível.</p>
            <?php endif; ?>
            <p> <?= $movie->description ?></p>
        </div>
        <div class="col-md-4">
            <div class="movie-image-container"
                style="background-image: url(<?= $BASE_URL ?>/img/movies/<?= $movie->image ?>);"></div>
        </div>
        <div class="offdet-md-1 col-md-10" id="reviews-container">
            <h3 class="reviews-title">Avaliações</h3>

            <!-- verifica se habilita a review ou não -->
            <?php if (!empty($userData) && !$userOwnsMovie && !$alreadyReviewed): ?>
            <div class="col-md-12" id="reviews-form-container">
                <h4 id="reviews-title">Envie sua avaliação</h4>
                <p class="pagee-description">Prencha o formulario com a nota e comentário sobre o filme</p>
                <form action="<?= $BASE_URL ?>review_process.php" id="review-form-container" method="POST">
                    <input type="hidden" name="type" value="create">
                    <input type="hidden" name="movies_id" value="<?= $movie->id ?>">
                    <div class="form-group">
                        <label for="rating">Nota do filme</label>
                        <select name="rating" id="rating" class="form-control">
                            <option value="">Selecione</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="review">Seu comentario:</label>
                        <textarea name="review" id="review" rows="3 " class="form-control"
                            placeholder="Deixe seu comentario sobre o filme"></textarea>

                    </div>
                    <input type="submit" class=" card-btn limit-btn" value="Enviar comentario">
                </form>
            </div>
            <?php endif; ?>
            <!-- comentarios -->

            <?php foreach ($moviesReviews as $review): ?>
            <?php require("templates/user_review.php") ?>
            <?php endforeach; ?>

            <?php if (count($moviesReviews) === 0): ?>
            <p class="empty-list">Ainda não há comentários para este filme</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once("templates/footer.php")
    ?>