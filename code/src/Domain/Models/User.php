<?php

namespace Geekbrains\Application1\Domain\Models;

use Geekbrains\Application1\Application\Application;
use Geekbrains\Application1\Infrastructure\Storage;

class User {

    private ?int $idUser;

    private ?string $userName;

    private ?string $userLastName;
    private ?int $userBirthday;

    private static string $storageAddress = '/storage/birthdays.txt';

    public function __construct(string $name = null, string $lastName = null, int $birthday = null, int $id_user = null){
        $this->userName = $name;
        $this->userLastName = $lastName;
        $this->userBirthday = $birthday;
        $this->idUser = $id_user;
    }

    public function setUserId(int $id_user): void {
        $this->idUser = $id_user;
    }

    public function getUserId(): ?int {
        return $this->idUser;
    }

    public function setName(string $userName) : void {
        $this->userName = $userName;
    }

    public function setLastName(string $userLastName) : void {
        $this->userLastName = $userLastName;
    }

    public function getUserName(): string {
        return $this->userName;
    }

    public function getUserLastName(): string {
        return $this->userLastName;
    }

    public function getUserBirthday(): int {
        return $this->userBirthday;
    }

    public function setBirthdayFromString(string $birthdayString) : void {
        $this->userBirthday = strtotime($birthdayString);
    }
    public static function getAllUsersFromStorage(): array {
        $sql = "SELECT * FROM users";

        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute();
        $result = $handler->fetchAll();

        $users = [];

        foreach($result as $item){
            $user = new User($item['user_name'], $item['user_lastname'], $item['user_birthday_timestamp'], $item['id_user']);
            $users[] = $user;
        }
        
        return $users;
    }

    public static function validateRequestData(): bool {
        $result = true;
        
        if (!( 
            isset($_POST['name']) && !empty($_POST['name']) &&
            isset($_POST['lastname']) && !empty($_POST['lastname']) &&
            isset($_POST['birthday']) && !empty($_POST['birthday'])
        )) {
            $result = false;
        }

        $_POST['name'] = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
        $_POST['lastname'] = htmlspecialchars($_POST['lastname'], ENT_QUOTES, 'UTF-8');
        
        if (!preg_match('/^(\d{2}-\d{2}-\d{4})$/', $_POST['birthday'])) {
            $result = false;
        }
    
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] != $_POST['csrf_token']) {
            $result = false;
        }
    
        return $result;
    }
    
    public static function getUserById(int $id): ?User {
        $sql = "SELECT * FROM users WHERE id_user = :id_user";
        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute(['id_user' => $id]);
        $result = $handler->fetch();
    
        if ($result) {
            return new User($result['user_name'], $result['user_lastname'], $result['user_birthday_timestamp'], $result['id_user']);
        }
        return null;
    }
    public static function exists(int $id): bool {
        $sql = "SELECT count(id_user) FROM users WHERE id_user = :id_user";
    
        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute([
            'id_user' => $id
        ]);
        $userCount = $handler->fetchColumn();
    
        return $userCount > 0;
    }
    
    
    
    public function updateUser(array $userDataArray): void {
        if (!isset($userDataArray['id_user']) || empty($userDataArray['id_user'])) {
            return;
        }
 
        $sql = "UPDATE users SET ";
        
        $counter = 0;
        $totalItems = count($userDataArray);

        foreach ($userDataArray as $key => $value) {
            if ($key !== 'id_user') {
                $sql .= $key . " = :" . $key;
                $counter++;
                if ($counter < $totalItems - 1) {
                    $sql .= ","; 
                }
            }
        }

        $sql .= " WHERE id_user = :id_user";
    
        $handler = Application::$storage->get()->prepare($sql);

        try {
            $handler->execute($userDataArray);
        } catch (Exception $e) {
        }
    }
    
    public function setParamsFromRequestData(): void {
        $this->userName = htmlspecialchars($_POST['name']);
        $this->userLastName = htmlspecialchars($_POST['lastname']);
        $this->setBirthdayFromString($_POST['birthday']); 
    }
    
    public static function deleteById(int $id): void {
        $sql = "DELETE FROM users WHERE id_user = :id_user";
        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute(['id_user' => $id]);
    }
    
    

    public function saveToStorage(){
        $sql = "INSERT INTO users(user_name, user_lastname, user_birthday_timestamp) VALUES (:user_name, :user_lastname, :user_birthday)";

        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute([
            'user_name' => $this->userName,
            'user_lastname' => $this->userLastName,
            'user_birthday' => $this->userBirthday
        ]);
    }

    public static function getUserRolesById(int $userId): array {
        $roles = ['user'];  

        $rolesSql = "SELECT role FROM user_roles WHERE id_user = :id";
        $handler = Application::$storage->get()->prepare($rolesSql);
        $handler->execute(['id' => $userId]);
        $result = $handler->fetchAll();

        if (!empty($result)) {
            foreach ($result as $role) {
                $roles[] = $role['role'];
            }
        }

        return $roles;
    }
    
}