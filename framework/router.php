<?php

Lib::Import('route');

class Router extends Extendable {
    public $routes = array();

    public function __construct($properties = array()) {
        parent::__construct($properties);
    }

    /**
     * @param $request
     * @return Route
     */
    public function route($request) {
        $request = new Request($request);

        foreach($this->routes as $route) {
            $pattern = $route->pattern;

            $pattern_pieces = $this->pieces($pattern);

            $uri = $request->getUri();

            $uri_pieces = $this->pieces($uri);

            if(count($pattern_pieces) == 0 && $uri == '/') {
                $route = new Route($route);

                return $route;
            }

            $named = array();
            $segments = array();

            $build_route = function() use($route, &$named, &$segments) {
                $route = new Route($route);

                $route->named = $named;

                $route->segments = $segments;

                return $route;
            };

            $matches = true;

            $wildcard = false;

            foreach($pattern_pieces as $index => $pattern_piece) {
                $uri_piece = isset($uri_pieces[$index]) ? $uri_pieces[$index] : '';

                if($pattern_piece == '*') {
                    if(($index + 1) > count($uri_pieces)) {
                        $matches = false;

                        break;
                    }

                    $segments = array_merge($segments, array_slice($uri_pieces, $index));

                    $wildcard = true;

                    return $build_route();
                }
                else if($pattern_piece[0] == ':') {
                    $segments[] = $uri_piece;

                    $named[substr($pattern_piece, 1)] = $uri_piece;

                    continue;
                }
                else if($pattern_piece == $uri_piece) {
                    $segments[] = $uri_piece;

                    continue;
                }

                $matches = false;

                break;
            }

            if(!$wildcard && count($pattern_pieces) != count($uri_pieces)) {
                $matches = false;
            }

            if(!$matches) {
                continue;
            }

            return $build_route();
        }

        return false;
    }

    protected function pieces($input) {
        $pieces = explode('/', $input);

        array_shift($pieces);

        $pieces = array_filter($pieces, function($piece) {
            return trim($piece) != '';
        });

        return $pieces;
    }
}