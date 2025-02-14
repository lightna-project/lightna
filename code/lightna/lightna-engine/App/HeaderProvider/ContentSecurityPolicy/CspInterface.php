<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider\ContentSecurityPolicy;

interface CspInterface
{

    /**
     * Set whether the policy is report only
     *
     * @param bool $value
     *
     * @return void
     */
    public function setReportOnly(bool $value): void;

    /**
     * Add a policy directive to the CSP header value
     *
     * @param string $directive
     * @param string $value
     *
     * @return void
     */
    public function addPolicy(string $directive, string $value): void;

    /**
     * Set the nonce source for the CSP header
     *
     * @param NonceSourceInterface $nonce
     *
     * @return void
     */
    public function setNonceSource(NonceSourceInterface $nonce): void;

    /**
     * Get the nonce for the CSP header value to allow inline scripts
     *
     * @return string
     */
    public function getNonce(): string;

    /**
     * Get name for the CSP header
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get value for the CSP header
     *
     * @return string
     */
    public function getValue(): string;

}