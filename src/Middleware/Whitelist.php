<?php

namespace AgenterLab\Firewall\Middleware;

use AgenterLab\Firewall\Abstracts\Middleware;

class Whitelist extends Middleware
{
    public function check($patterns)
    {
        return ($this->isWhitelist() === false);
    }
}
