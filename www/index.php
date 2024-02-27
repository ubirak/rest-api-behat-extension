<?php

/**
 * Totally copied from https://github.com/Behat/WebApiExtension.
 */
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

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
    'error_random',
    function (Request $request) {
        $statusCode = time() % 3 <= 0 ? 200 : 502;

        return new JsonResponse([], $statusCode);
    }
);
$app->match(
    'always_error',
    function (Request $request) {
        return new JsonResponse([], 502);
    }
);

$app->match(
    'post-html-form',
    function (Request $request) {   
        return new JsonResponse([
            'content_type_header_value' => $request->headers->get('content-type'),
            'post_fields_count' => $request->request->count(),
            'post_fields' => $request->request->all(),
        ]);
    }
);

$app->match(
    'post-html-form-with-files',
    function (Request $request) {   
        return new JsonResponse([
            'content_type_header_value' => $request->headers->get('content-type'),
            'post_files_count' => count($request->files),
            'post_fields_count' => $request->request->count(),
            'post_fields' => $request->request->all(),
        ]);
    }
);

$app->match(
    'response_content_plain_text_inspection',
    function (Request $request) {   
        $data = json_decode($request->getContent(), true);    
        return new Response($data['plain_text_value_i_want_to_have_in_response_body_content'], 200);
    }
);

$app->run();
