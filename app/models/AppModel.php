<?php

namespace models;

use widgets\user\User;

class AppModel
{
    public function redirect($controller, $event = '') {
        $location = 'Location: ' . HTTP . '://' . '/' . NAME_WEBSITE . '/' . $controller . '/' . $event;
        header($location);
        die();
    }

    protected function getUserFio($db):string {
        $user = new User($db);
        $currentUser = $user->getUsers($_COOKIE['user'])[0];
        return $currentUser['lastname'] . ' ' . mb_substr($currentUser['name'], 0, 1) . '.' . mb_substr($currentUser['patronymic'], 0, 1) . '.';
    }
}