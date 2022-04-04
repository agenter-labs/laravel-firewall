<?php

namespace AgenterLab\Firewall\Tests\Feature;

use AgenterLab\Firewall\Middleware\Whitelist;
use AgenterLab\Firewall\Models\Ip as Model;
use AgenterLab\Firewall\Tests\TestCase;

class WhitelistTest extends TestCase
{
    public function testShouldAllow()
    {
        config(['firewall.whitelist' => ['127.0.0.0/24']]);

        $this->assertEquals('next', (new Whitelist())->handle($this->app->request, $this->getNextClosure()));
    }

    public function testShouldBlock()
    {
        $this->assertEquals('403', (new Whitelist())->handle($this->app->request, $this->getNextClosure())->getStatusCode());
    }
}
