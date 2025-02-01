<?php

require __DIR__ . '/../src/Exceptions/HttpException.php';

use Exceptions\HttpException;

$routes = include '../src/routes.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = ltrim($path, '/');

// Look for an exact match first
if (isset($routes[$path])) {
    $handler = $routes[$path];
} else {
    $handler = null;
    foreach ($routes as $pattern => $routeHandler) {
        $regex = '/^' . str_replace('/', '\/', $pattern) . '$/';
        if (preg_match($regex, $path, $matches)) {
            array_shift($matches);
            $handler = function () use ($routeHandler, $matches) {
                return $routeHandler(...$matches);
            };
            break;
        }
    }
}

if ($handler) {
    try {
        $renderer = $handler($path);
        // Set raw HTTP headers
        foreach ($renderer->getFields() as $name => $value) {
            // Content-Length は数値なのでサニタイズしない
            if ($name === 'Content-Length') {
                header("{$name}: {$value}");
                continue;
            }
            $sanitized_value = htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');
            if ($sanitized_value && $sanitized_value === $value) {
                header("{$name}: {$sanitized_value}");
            } else {
                throw new Exception('Failed setting header - original: ' . $value . ', sanitized: ' . $sanitized_value);
            }
        }
        echo $renderer->getContent();
    } catch (HttpException $e) {
        http_response_code($e->getStatusCode());
        echo $e->getStatusCode() . ' ' . $e->getErrorMessage();
    } catch (Exception $e) {
        http_response_code(500);
        echo $e->getMessage();
    }
} else {
    http_response_code(404); // public/404.html
}
