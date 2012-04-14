<?php

include_once('libs.php');

Lib::Import(array('request', 'config', 'router', 'response', 'resource', 'view_response', 'redirect_response',
    'strlib'));

Config::Import('routes');

class Framework {
    public function run() {
        $request = new Request(array(
            'verb' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'query_string' => $_SERVER['QUERY_STRING'],
            'data' => $_POST
        ));

        $router = new Router(array(
            'routes' => Config::$Configs['routes']
        ));

        $route = $router->route($request);

        if(false === $route) {
            $response = new Response(array(
                'status' => '404'
            ));
        }
        else {
            $resource = $route->resource;

            $lib = 'resources/' . preg_replace('/\\_?resource$/', '', strtolower($resource));

            Lib::Import($lib);

            $resource = new $resource(array(
                'route' => $route,
                'request' => $request
            ));

            $pre_execute = $resource->pre_execute();

            if(is_object($pre_execute)) {
                $response = $pre_execute;
            }
            else {
                $response = $resource->execute();
            }
        }

        if(empty($response)) {
            return;
        }

        $response->send();
    }
}