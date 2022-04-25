<?php

namespace Ubirak\RestApiBehatExtension\Html;

class Form
{
    private $body = [];

    private $contentTypeHeaderValue = '';

    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        if ($this->bodyHasFileObject()) {
            return $this->getMultipartStreamBody();
        }

        return $this->getNameValuePairBody();
    }

    /**
     *
     * @return string
     */
    public function getContentTypeHeaderValue()
    {
        return $this->contentTypeHeaderValue;
    }

    /**
     *
     * @param string $value
     */
    private function setContentTypeHeaderValue($value)
    {
        $this->contentTypeHeaderValue = $value;
    }

    /**
     *
     * @return boolean
     */
    private function bodyHasFileObject()
    {
        foreach ($this->body as $element) {
            if ($element['object'] == 'file') {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return \GuzzleHttp\Psr7\MultipartStream
     */
    private function getMultipartStreamBody()
    {
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

    /**
     *
     * @return string
     */
    private function getNameValuePairBody()
    {
        $body = [];
        foreach ($this->body as $element) {
            $body[$element['name']] = $element['value'];
        }

        $this->setContentTypeHeaderValue('application/x-www-form-urlencoded');

        return http_build_query($body);
    }

}
