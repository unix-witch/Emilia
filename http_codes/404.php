<?php

function c404() {
    http_response_code(404);
    require './errors/404.php';
    die;
}



?>