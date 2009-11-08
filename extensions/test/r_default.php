<?php

class testone
{
    static function test_extension($test)
    {
        tpl::set('test', $test);
    }
}

function test_extension2()
{
	tpl::set('test', 'HOHO!');
}

// Argument order: hook name, function name, priority (lower numbers are executed first)
// and the number of arguments the function accepts
extensions::add_action('test_hook', 'testone::test_extension', 10, 1);
extensions::add_action('test_hook', 'test_extension2', 11, 3);
