<?php

namespace {

    function touchMyTrallala($content)
    {
        file_put_contents(ROOT_TESTS . '/data/temp/ping.txt', $content);
    }

}