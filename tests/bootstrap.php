<?php

require dirname(__DIR__) . '/vendor/autoload.php';

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__,
    get_include_path()
)));
