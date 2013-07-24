<?php

namespace Xi\Filelib\Renderer;

class Response
{

    private $statusCode = 200;

    private $headers = array();

    private $content;


    public function __construct()
    {
        $this->content = function () {
            return '';
        };
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function setContent($content)
    {
        if (!is_callable($content)) {
            throw new \InvalidArgumentException('Content must be a callable');
        }

        $this->content = $content;

        return $this;
    }

    public function getHeader($key, $default = null)
    {
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
        return $default;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getContent()
    {
        return call_user_func($this->content);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
