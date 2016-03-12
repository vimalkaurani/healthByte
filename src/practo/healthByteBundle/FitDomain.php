<?php
/**
 * Created by PhpStorm.
 * User: anushilnandan
 * Date: 19/08/15
 * Time: 12:16 AM.
 */
namespace practo\healthByteBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class FitDomain
 *
 * @package Fit\ContentBundle
 */
class FitDomain
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack - Request
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack;
    }

    /**
     * Get Host.
     *
     * @param string $subdomain - Subdomain
     *
     * @return string
     */
    public function getHost($subdomain = null)
    {
 
    
        $fitHost = $this->request->getCurrentRequest()->getSchemeAndHttpHost();
        $fitHostRaw = $this->request->getCurrentRequest()->getHost();
        if (!$subdomain) {

            return $fitHost;
        }

        $origSubdomain = explode('.', $fitHostRaw);
        $origSubdomain = $origSubdomain[0];
        $origSubdomain = explode('-', $origSubdomain);
        $origSubdomain = $origSubdomain[0];

        return str_replace($origSubdomain, $subdomain, $fitHost);
    }

    /**
     * Get Current Url.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->request->getUri();
    }
}
