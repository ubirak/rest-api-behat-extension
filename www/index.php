<?php

/**
 * Totally copied from https://github.com/Behat/WebApiExtension
 */

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app->match(
    'echo',
    function (Request $req) {
        $ret = array(
            'warning' => 'Do not expose this service in production : it is intrinsically unsafe',
        );

        $ret['method'] = $req->getMethod();

        // Forms should be read from request, other data straight from input.
        $requestData = $req->request->all();
        if (!empty($requestData)) {
            foreach ($requestData as $key => $value) {
                $ret[$key] = $value;
            }
        }

        /** @var string $content */
        $content = $req->getContent(false);
        if (!empty($content)) {
            $data = json_decode($content, true);
            if (!is_array($data)) {
                $ret['content'] = $content;
            } else {
                foreach ($data as $key => $value) {
                    $ret[$key] = $value;
                }
            }
        }

        $ret['headers'] = array();
        foreach ($req->headers->all() as $k => $v) {
            $ret['headers'][$k] = $v;
        }
        foreach ($req->query->all() as $k => $v) {
            $ret['query'][$k] = $v;
        }
        $response = new JsonResponse($ret);

        return $response;
    }
);

$app->match(
    'redirect',
    function (Request $req) {

        return new RedirectResponse('do-not-expose-this-service-in-production.it/is-intrinsically-unsafe', Response::HTTP_MOVED_PERMANENTLY);
    }
);

$app->run();
