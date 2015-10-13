<?php

namespace Bolt\Extension\Bolt\ClientLogin\Tests;

use Bolt\Extension\Bolt\ClientLogin\Extension;
use Bolt\Tests\BoltUnitTest;

/**
 * Base class for ClientLogin testing.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractExtensionUnitTest extends BoltUnitTest
{
    /** \Bolt\Application */
    protected $app;

    protected function getApp($boot = true)
    {
        if ($this->app) {
            return $this->app;
        }

        $app = parent::getApp($boot);
        $extension = new Extension($app);

        $app['extensions']->register($extension);

        return $this->app = $app;
    }

    protected function getExtension()
    {
        if ($this->app === null) {
            $this->getApp();
        }

        return $this->app['extensions.ClientLogin'];
    }
}
