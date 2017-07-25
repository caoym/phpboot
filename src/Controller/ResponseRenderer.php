<?php

namespace PhpBoot\Controller;


class ResponseRenderer
{
    /**
     * @param mixed $content
     * @return string
     */
    public function render($content)
    {
        return json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}