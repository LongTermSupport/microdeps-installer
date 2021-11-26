<?php

declare(strict_types=1);

namespace Foo\Bar\Tests;

use Foo\Bar\Biz;

class BizTest
{
    public function getBiz(): Biz
    {
        return new Biz();
    }
}