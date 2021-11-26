<?php

declare(strict_types=1);

namespace Foo\Bar\Baz;

use Foo\Bar\Baz\Taz\Waz;
use Foo\Bar\Biz;

class Bop
{
    public function __construct(public Waz $waz, public Biz $biz)
    {

    }
}