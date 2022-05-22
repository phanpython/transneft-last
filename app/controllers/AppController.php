<?php

namespace controllers;

use core\base\Controller;

class AppController extends Controller
{
    protected function checkAuthorization() {
        if(!isset($_COOKIE['user']) || isset($_POST['exit'])) {
            if(isset($_POST['exit'])) {
                $this->exitFromProfile();
            }
            header('Location: http://trans');
            die();
        }
    }

    protected function prepareVars($arr) {
        foreach ($arr as $key=>$value) {
            $arr[$key] = htmlspecialchars(trim($value));
        }

        return $arr;
    }

    protected function exitFromProfile() {
        $userId = intval($_COOKIE['user']);
        setcookie('user', '', 0, '/');
    }
}