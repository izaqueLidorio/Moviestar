<?php

class Message
{

    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }


    public function setMessage($msg, $type, $redirect = "index.php")
    {
        // Inicia a sessão caso ainda não esteja ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Salva mensagem na sessão
        $_SESSION["msg"] = $msg;
        $_SESSION["type"] = $type;

        // Se o redirecionamento NÃO for para "back", usa a URL fornecida
        if ($redirect !== "back") {
            header("Location: " . $redirect);
            exit(); // Encerra o script para garantir o redirecionamento
        }
        // Se for "back", tenta redirecionar para a página anterior
        else {
            if (!empty($_SERVER["HTTP_REFERER"])) {
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit();
            } else {
                // Se não houver página anterior, redireciona para o index
                header("Location: index.php");
                exit();
            }
        }
    }

    public function getMessage()
    {
        if (!empty($_SESSION["msg"])) {
            $msg = $_SESSION["msg"];
            $type = $_SESSION["type"];

            // Limpa a mensagem da sessão
            unset($_SESSION["msg"]);
            unset($_SESSION["type"]);

            return ["msg" => $msg, "type" => $type];
        }
        return false;
    }


    public function clearMessage()
    {

        $_SESSION["msg"] = "";
        $_SESSION["type"] = "";

    }
}