<?php
require_once("models/user.php");
require_once("models/Message.php");

class UserDAO implements UserDAOInterface
{

    private $conn;
    private $url;
    private $message;

    public function __construct(Pdo $conn, $url)
    {
        $this->conn = $conn;
        $this->url = $url;
        $this->message = new Message($url);
    }

    public function buildUser($data)
    {
        $user = new User();
        $user->id = $data["id"];
        $user->name = $data["name"];
        $user->lastname = $data["lastname"];
        $user->email = $data["email"];
        $user->password = $data["password"];
        $user->image = $data["image"];
        $user->bio = $data["bio"];
        $user->token = $data["token"];

        return $user;

    }


    public function create(User $user, $authUser = false)
    {
        echo "Método create foi chamado!"; // Verifique se essa mensagem aparece
        // Restante do código...
        try {
            $stmt = $this->conn->prepare("INSERT INTO users(
           name, lastname, email, password, token) 
           VALUES( :name, :lastname, :email, :password, :token)");

            $stmt->bindParam(":name", $user->name);
            $stmt->bindParam(":lastname", $user->lastname);
            $stmt->bindParam(":email", $user->email);
            $stmt->bindParam(":password", $user->password);
            $stmt->bindParam(":token", $user->token);

            $stmt->execute();

        } catch (PDOException $e) {
            echo "Erro ao executar a consulta: " . $e->getMessage();
            print_r($stmt->errorInfo()); // Mostra detalhes do erro
        }
        //Autenticar usuario caso auth seja true
        if ($authUser) {
            $this->setTokenToSession($user->token);
        }
    }




    public function update(User $user, $redirect = true)
    {

        $stmt = $this->conn->prepare("UPDATE users SET 
         name = :name,
         lastname = :lastname,
         email = :email,
         image = :image,
         bio = :bio,
         token = :token
         WHERE id = :id");

        $stmt->bindParam(":name", $user->name);
        $stmt->bindParam(":lastname", $user->lastname);
        $stmt->bindParam(":email", $user->email);
        $stmt->bindParam(":image", $user->image);
        $stmt->bindParam(":bio", $user->bio);
        $stmt->bindParam(":token", $user->token);
        $stmt->bindParam(":id", $user->id);

        $stmt->execute();

        if ($redirect) {
            // redireciona para o perfil de usuario
            $this->message->setMessage("Dados atualizados com sucesso!!", "success", redirect: "editprofile.php");
        }

    }



    public function verifyToken($protected = false)
    {

        if (!empty($_SESSION["token"])) {
            //pega o token da session
            $token = $_SESSION["token"];

            $user = $this->findByToken($token);

            if ($user) {
                return $user;
            } else if ($protected) {
                //redireciona usuario não autenticado
                $this->message->setMessage(
                    "Faça a autenticação para acessar esta página !!",
                    "error",
                    redirect: "index.php"
                );
            }


        } else if ($protected) {
            //redireciona usuario não autenticado
            $this->message->setMessage(
                "Faça a autenticação para acessar esta página !!",
                "error",
                redirect: "index.php"
            );
        }

    }



    public function setTokenToSession($token, $redirect = true)
    {
        // salvar token da session
        $_SESSION["token"] = $token;

        if ($redirect) {
            // redireciona para o perfil de usuario
            $this->message->setMessage("Seja bem-vindo!!", "success", redirect: "editprofile.php");
        } else {
            echo "algo deu errado";
        }
    }




    public function authenticateUser($email, $password)
    {

        $user = $this->findByEmail($email);

        if ($user) {
            //checar se as senhas batem 
            if (password_verify($password, $user->password)) {

                //gerar um token e inserir na session
                $token = $user->generateToken();
                $this->setTokenToSession($token, false);

                //Atualizar token  no usuario
                $user->token = $token;

                $this->update($user, false);

                return true;


            } else {
                return false;
            }
        } else {
            return false;
        }

    }





    public function findByEmail($email)
    {

        if ($email != "") {

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");         //buscar um usuário com o email fornecido   
            $stmt->bindParam(":email", $email);                    // Associa o valor da variável $email ao parâmetro :email na consulta SQL
            $stmt->execute();

            if ($stmt->rowCount() > 0) {                                           // Se houver resultados, pega a primeira linha de dados

                $data = $stmt->fetch();
                $usuário = $this->buildUser($data);                 // Converte os dados do banco em um objeto de usuário usando o método buidUser

                return $usuário;                                       // Retorna o objeto de usuário
            } else {

                return false;                                          // Se não houver resultados, retorna falso
            }
        } else {

            return false;                                                // Se o email estiver vazio, retorna falso
        }
    }


    public function findById($id)
    {
          
        if ($id != "") {

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");         //buscar um usuário com o id fornecido   
            $stmt->bindParam(":id", $id);                    // Associa o valor da variável $email ao parâmetro :email na consulta SQL
            $stmt->execute();

            if ($stmt->rowCount() > 0) {                                           // Se houver resultados, pega a primeira linha de dados

                $data = $stmt->fetch();
                $usuário = $this->buildUser($data);                 // Converte os dados do banco em um objeto de usuário usando o método buidUser

                return $usuário;                                       // Retorna o objeto de usuário
            } else {

                return false;                                          // Se não houver resultados, retorna falso
            }
        } else {

            return false;                                                // Se o email estiver vazio, retorna falso
        }
    }


    public function findByToken($token)
    {

        if ($token != "") {

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE token = :token");
            $stmt->bindParam(":token", $token);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $data = $stmt->fetch();
                $usuário = $this->buildUser($data);

                return $usuário;
            } else {

                return false;
            }
        } else {

            return false;
        }
    }

    public function destroyToken()
    {
        //remove token da session
        $_SESSION["token"] = "";
        //redireciona e apresenta a msg de sucess
        $this->message->setMessage("Voce fez o lougout com sucesso!", "success", "index.php");
    }


    public function changePassword(User $user)
    {

        $stmt = $this->conn->prepare("UPDATE users SET 
        password = :password
        WHERE id = :id");

        $stmt->bindParam(":password", $user->password);
        $stmt->bindParam(":id", $user->id);

        $stmt->execute();


        // mensagem de sucesso
        $this->message->setMessage("senha atualizada com sucesso", "success", redirect: "editprofile.php");

    }

}