<?php
namespace App\Controller;

use SpringPHP\Core\Controller;

class Error extends Controller{
    public function index404(){
        return 'Error404';
    }
}