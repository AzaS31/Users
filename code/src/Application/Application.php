<?php

namespace Geekbrains\Application1\Application;

use Geekbrains\Application1\Domain\Controllers\AbstractController;
use Geekbrains\Application1\Infrastructure\Config;
use Geekbrains\Application1\Infrastructure\Storage;
use Geekbrains\Application1\Application\Auth;

class Application {

    private const APP_NAMESPACE = 'Geekbrains\Application1\Domain\Controllers\\';

    private string $controllerName;
    private string $methodName;

    public static Config $config;

    public static Storage $storage;

    public static Auth $auth;

    public function __construct(){
        Application::$config = new Config();
        Application::$storage = new Storage();
        Application::$auth = new Auth();
    }

    public function run(): string {
        session_start();
    
        // Разбиваем URL на части
        $routeArray = explode('/', $_SERVER['REQUEST_URI']);
    
        // Проверяем, что есть второй элемент (контроллер)
        if(isset($routeArray[1]) && $routeArray[1] != '') {
            $controllerName = $routeArray[1];
        } else {
            $controllerName = "page"; // Если нет контроллера, берем default
        }
    
        $this->controllerName = Application::APP_NAMESPACE . ucfirst($controllerName) . "Controller";
    
        // Проверяем, что класс контроллера существует
        if(class_exists($this->controllerName)) {
            // Проверяем, есть ли метод (по умолчанию index)
            if(isset($routeArray[2]) && $routeArray[2] != '') {
                $methodName = $routeArray[2];
            } else {
                $methodName = "index"; // Если нет метода, берем default
            }
    
            $this->methodName = "action" . ucfirst($methodName);
    
            // Проверяем, существует ли метод в контроллере
            if(method_exists($this->controllerName, $this->methodName)) {
                $controllerInstance = new $this->controllerName();
    
                // Получаем параметр id, если он есть
                $id = isset($routeArray[3]) ? $routeArray[3] : null;
    
                // Вызываем метод с параметром id
                return call_user_func_array(
                    [$controllerInstance, $this->methodName],
                    [$id] // передаем параметр id в метод контроллера
                );
            } else {
                throw new Exception("Метод $this->methodName не существует", 404);
            }
        } else {
            throw new Exception("Класс $this->controllerName не существует", 404);
        }
    }
    

    private function checkAccessToMethod(AbstractController $controllerInstance, string $methodName): bool {
        $userRoles = $controllerInstance->getUserRoles();



        $rules = $controllerInstance->getActionsPermissions($methodName);

        $rules[] = 'user';

        $isAllowed = false;

        if(!empty($rules)){
            foreach($rules as $rolePermission){
                if(in_array($rolePermission, $userRoles)){
                    $isAllowed = true;
                    break;
                }
            }
        }

        return $isAllowed;

    }
}