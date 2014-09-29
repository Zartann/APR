<?php

namespace APR\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class APRUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
