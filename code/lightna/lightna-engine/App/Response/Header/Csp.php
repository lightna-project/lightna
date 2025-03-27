<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

use Lightna\Engine\App\Security\Csp as SecurityCsp;

/**
 * Adds a Content-Security-Policy header to HTTP responses to safeguard against cross-site scripting.
 */
class Csp extends AbstractHeader
{
    protected SecurityCsp $securityCsp;
    protected string $name = 'Content-Security-Policy';

    public function getValue(): string
    {
        $directives = [];
        foreach ($this->securityCsp->getPolicies() as $name => $values) {
            $filteredValues = array_keys(array_filter($values));
            if (count($filteredValues)) {
                $directives[] = $name . ' ' . implode(' ', $filteredValues);
            }
        }

        return implode('; ', $directives);
    }
}
