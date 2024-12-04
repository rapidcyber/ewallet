<?php

namespace App\Services\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Basic as BasePolicy;

class Policy extends BasePolicy
{
    public function configure()
    {
        parent::configure();
        $this->addGeneralDirectives();
    }

    protected function addGeneralDirectives()
    {
        return $this
            ->addNonceForDirective(Directive::SCRIPT)
            ->addNonceForDirective(Directive::STYLE)
            ->addDirective(Directive::FONT, ['self', 'data:'])
            ->addDirective(Directive::SCRIPT, [
                'self',
                'unsafe-eval',
                'https://www.googletagmanager.com/gtm.js',
                'https://www.google.com/recaptcha/'
            ])
            ->addDirective(Directive::FRAME, ['self', 'https://www.google.com']);
    }
}
