<?php

namespace ATUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ATUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
