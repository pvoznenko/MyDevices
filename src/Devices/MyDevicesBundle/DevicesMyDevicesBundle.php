<?php

namespace Devices\MyDevicesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DevicesMyDevicesBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
