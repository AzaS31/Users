<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Application\Application;
use Geekbrains\Application1\Application\Render;
use Geekbrains\Application1\Application\Auth;
use Geekbrains\Application1\Domain\Models\User;

class UserController extends AbstractController {

    protected array $actionsPermissions = [
        'actionHash' => ['admin'],
        'actionSave' => ['admin'],
        'actionEdit' => ['admin'],
        'actionIndex' => ['admin'],
        'actionLogout' => ['admin'],
        'actionUpdate' => ['admin'],
    ];

    public function actionIndex(): string {
        $users = User::getAllUsersFromStorage();
        
        $render = new Render();

        if(!$users){
            return $render->renderPage(
                'user-empty.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'message' => "Список пуст или не найден"
                ]);
        }
        else{
            return $render->renderPage(
                'user-index.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'users' => $users
                ]);
        }
    }

    public function actionSave(): string {
        if(User::validateRequestData()) {
            $user = new User();
            $user->setParamsFromRequestData();
            $user->saveToStorage();

            $render = new Render();

            return $render->renderPage(
                'user-created.tpl', 
                [
                    'title' => 'Пользователь создан',
                    'message' => "Создан пользователь " . $user->getUserName() . " " . $user->getUserLastName()
                ]);
        }
        else {
            throw new \Exception("Переданные данные некорректны");
        }
    }

    public function actionUpdate(): string { 
        if (isset($_POST['id_user']) && User::exists($_POST['id_user'])) {
            $user = new User();
            $user->setUserId($_POST['id_user']);
    
            $arrayData = [];

            if (isset($_POST['name'])) {
                $arrayData['user_name'] = htmlspecialchars(trim($_POST['name']));
            }

            if (isset($_POST['lastname'])) {
                $arrayData['user_lastname'] = htmlspecialchars(trim($_POST['lastname']));
            }

            if (isset($_POST['birthday'])) {
                $arrayData['user_birthday_timestamp'] = strtotime($_POST['birthday']);
            }

            $arrayData['id_user'] = $_POST['id_user'];

            if (!empty($arrayData)) {
                try {
                    $user->updateUser($arrayData);
                    $message = "Пользователь с ID: " . $user->getUserId() . " обновлен успешно!";
                } catch (\Exception $e) {
                    $message = "Ошибка обновления пользователя: " . $e->getMessage();
                }
            } else {
                $message = "Нет данных для обновления.";
            }

            $render = new Render();
            return $render->renderPage(
                'user-updated.tpl',
                [
                    'title' => 'Пользователь обновлен',
                    'message' => $message
                ]
            );
        } else {
            throw new \Exception("Пользователь не существует");
        }
    }
    
    public function actionDelete(int $id): string {
        if (User::exists($id)) {
            try {
                User::deleteById($id);
                $message = "Пользователь с ID: $id успешно удален!";
            } catch (\Exception $e) {
                $message = "Ошибка удаления пользователя: " . $e->getMessage();
            }
        } else {
            $message = "Пользователь с таким ID не найден.";
        }
    
        $render = new Render();
        return $render->renderPage(
            'user-deleted.tpl',
            [
                'title' => 'Удаление пользователя',
                'message' => $message
            ]
        );
    }
    
      

    public function actionEdit(int $id): string {
        $user = User::getUserById($id); 
        $render = new Render();
        return $render->renderPageWithForm(
            'user-form.tpl', 
            [
                'title' => 'Редактирование пользователя',
                'user' => $user
            ]
        );
    }
    

    public function actionAuth(): string {
        $render = new Render();
        
        return $render->renderPageWithForm(
                'user-auth.tpl', 
                [
                    'title' => 'Форма логина'
                ]);
    }

    public function actionHash(): string {
        return Auth::getPasswordHash($_GET['pass_string']);
    }

    public function actionLogin(): string {
        $result = false;
    
        if (isset($_POST['login']) && isset($_POST['password'])) {
            $result = Application::$auth->proceedAuth($_POST['login'], $_POST['password']);
        }
    
        if (!$result) {
            $render = new Render();
            return $render->renderPageWithForm(
                'user-auth.tpl',
                [
                    'title' => 'Форма логина',
                    'auth-success' => false,
                    'auth-error' => 'Неверные логин или пароль'
                ]
            );
        } else {
            $_SESSION['user_name'] = $_POST['login'];
            header('Location: /');
            return "";
        }
    }
    
    public function actionLogout(): void {
        session_destroy();
        unset($_SESSION['user_name']);
        header("Location: /");
        die();
    }    
}