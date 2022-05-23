<?php

function c403() {
    http_response_code(403);
    require __DIR__ . './errors/403.php';
    die;
}



?>