<?php

class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password;
    public $name;
    public $surname;
    public $secondname;
    public $post;
    public $birthday;
    public $avatar;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Метод создания нового пользователя.
    function create(): bool
    {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    email = :email,
                    password = :password,
                    name = :name,
                    surname = :surname,
                    secondname = :secondname,
                    post = :post,
                    birthday = :birthday,
                    avatar = :avatar";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->surname = htmlspecialchars(strip_tags($this->surname));
        $this->secondname = htmlspecialchars(strip_tags($this->secondname));
        $this->post = htmlspecialchars(strip_tags($this->post));
        $this->birthday = htmlspecialchars(strip_tags($this->birthday));

        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":surname", $this->surname);
        $stmt->bindParam(":secondname", $this->secondname);
        $stmt->bindParam(":post", $this->post);
        $stmt->bindParam(":birthday", $this->birthday);
        $stmt->bindParam(":avatar", $this->avatar);

        // Хешируем пароль перед сохранением в базу данных.
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        echo $stmt->bindParam(":password", $password_hash);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Получение данных пользователя.
    function get(): array
    {
        $query = "SELECT id,
                  email,
                  password,
                  name,
                  surname,
                  secondname,
                  post,
                  birthday,
                  avatar FROM " . $this->table_name . "
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else return [];
    }

    // Обновление данных пользователя.
    public function update(): bool
    {
        $password_set = !empty($this->password) ? " password = :password" : "";

        // Если не введен пароль - не обновлять пароль.
        $query = "UPDATE " . $this->table_name . "
            SET
                email = :email,
                {$password_set},
                name = :name,
                surname = :surname,
                secondname = :secondname,
                post = :post,
                birthday = :birthday,
                avatar = :avatar
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->surname=htmlspecialchars(strip_tags($this->surname));
        $this->secondname=htmlspecialchars(strip_tags($this->secondname));
        $this->post=htmlspecialchars(strip_tags($this->post));
        $this->birthday=htmlspecialchars(strip_tags($this->birthday));

        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":surname", $this->surname);
        $stmt->bindParam(":secondname", $this->secondname);
        $stmt->bindParam(":post", $this->post);
        $stmt->bindParam(":birthday", $this->birthday);
        $stmt->bindParam(":avatar", $this->avatar);

        if (!empty($this->password)) {
            $this->password = htmlspecialchars(strip_tags($this->password));
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(":password", $password_hash);
        }

        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $query = "DELETE FROM " . $this->table_name . "
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Проверка, существует ли электронная почта в нашей базе данных.
    function emailExists(): bool
    {
        $query = "SELECT id, password
            FROM " . $this->table_name . "
            WHERE email = ?
            LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $this->email=htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(1, $this->email);

        $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row["id"];
            $this->password = $row["password"];

            return true;
        }

        return false;
    }
}