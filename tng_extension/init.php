<?php

use Bolt\Extension\Bolt\ClientLogin\Extension;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}
