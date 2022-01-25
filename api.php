<?php
define('METHOD', $_SERVER['REQUEST_METHOD']);
define('URI', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

require_once __DIR__ . '/api/controllers/read.php';

function router($url, ...$args)
{
    (empty($args[1]) || false !== strpos(METHOD, $args[0]))
    && (URI === $url || preg_match('#^' . $url . '$#iu', URI, $match))
    && die(call_user_func_array(end($args), $match ?? []));
}

router('/api/products', 'GET', function () {
    getProducts();
});

router('/api/product/(\d+)', 'GET', function (...$args) {
    getProduct($args[1]);
});

router('/api/product', 'POST', function () {
    createProduct();
});

router('/api/product/(\d+)', 'PUT', function (...$args) {
    update($args[1]);
});

router('/api/games/(\d+)', 'DELETE', function (...$args) {
    echo ' удалить игрушку: ', $args[1];
});

header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
echo '404 Страница не найдена';