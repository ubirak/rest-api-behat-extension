<?php

namespace Ubirak\RestApiBehatExtension\Html;

class Form {

    private $body = [];
    private $contentTypeHeaderValue = '';

    public function __construct(array $body) {
        $this->body = $body;
    }

    public function getBody() {

        if ($this->bodyHasFileObject()) {
            return $this->getMultipartStreamBody();
        }

        return $this->getNameValuePairBody();
    }

    public function getContentTypeHeaderValue(): string {
        return $this->contentTypeHeaderValue;
    }

    private function setContentTypeHeaderValue(string $value) {
        $this->contentTypeHeaderValue = $value;
    }

    private function bodyHasFileObject(): bool {

        foreach ($this->body as $element) {
            if ($element['object'] == 'file') {
                return true;
            }
        }

        return false;
    }

    private function getMultipartStreamBody(): \GuzzleHttp\Psr7\MultipartStream {

        $multiparts = array_map(
                function ($element) {

            if ($element['object'] == 'file') {
                return ['name' => $element['name'], 'contents' => fopen($element['value'], 'r')];
            }

            return ['name' => $element['name'], 'contents' => $element['value']];
        }, $this->body
        );

        $boundary = sha1(uniqid('', true));

        $this->setContentTypeHeaderValue('multipart/form-data; boundary=' . $boundary);

        return new \GuzzleHttp\Psr7\MultipartStream($multiparts, $boundary);
    }

    private function getNameValuePairBody(): string {

        $body = [];
        foreach ($this->body as $element) {
            $body[$element['name']] = $element['value'];
        }

        $this->setContentTypeHeaderValue('application/x-www-form-urlencoded');

        return http_build_query($body, null, '&');
        ;
    }

}
