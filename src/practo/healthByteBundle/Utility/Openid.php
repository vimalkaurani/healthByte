<?php


namespace practo\healthByteBundle\Utility;

use practo\healthByteBundle\FitDomain;
use Auth_OpenID_Consumer;
use Auth_OpenID_MemcachedStore;

/**
 * Class Openid.
 */
class Openid
{
    /**
     * @var
     */
    protected $cache;
    /**
     * @var
     */
    protected $fitDomain;
    /**
     * @var
     */
    protected $memcachedHost;
    /**
     * @var
     */
    protected $memcachedPort;

    /**
     * @param string $memcachedHost
     * @param string $memcachedPort
     */
    public function __construct($memcachedHost, $memcachedPort)
    {
        $this->memcachedHost = $memcachedHost;
        $this->memcachedPort = $memcachedPort;
    }

    /**
     * Set Fabric Domain.
     *
     * @param FitDomain $fitDomain - Fit Domain
     */
    public function setFitDomain(FitDomain $fitDomain = null)
    {
        $this->fitDomain = $fitDomain;
    }


    /**
     * @param null $next
     * @param null $intent
     * @param null $redirectTo
     *
     * @return $this|string
     *
     * @throws \Exception
     */
    public function requestForm($next = null, $intent = null, $redirectTo = null)
    {
        $consumer = $this->getConsumer();
        $fitHost = $this->fitDomain->getHost();
        $accountsHost = $this->fitDomain->getHost('accounts');
        $trustRoot = $fitHost;

        // Begin the OpenID authentication process.
        $authRequest = $consumer->begin($accountsHost);
        $returnTo = $this->getReturnTo();
        
        if (!$authRequest) {
            throw new \Exception('Authentication error; not a valid OpenID in '.__METHOD__);
        }

        $axRequest = new \Auth_OpenID_AX_FetchRequest();

        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/namePerson', 1, true, 'name'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/email', 1, true, 'email'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/phone', 1, true, 'mobile'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/phone/additional', 1, true, 'phoneNos'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/gender', 1, true, 'gender'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/birthDate', 1, true, 'dob'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/blood_group', 1, true, 'bloodGroup'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/postalAddress/line1', 1, true, 'street'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/postalAddress/line2', 1, true, 'locality'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/city', 1, true, 'city'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/state', 1, true, 'state'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/country', 1, true, 'country'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/postalCode', 1, true, 'pincode'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/profile_picture_url', 1, true, 'photoHash'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/timezone', 1, true, 'timezone'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/contact/referrer', 1, true, 'referrer'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/mobile_verified', 1, true, 'mobileVerified'));
        $axRequest->add(new \Auth_OpenID_AX_AttrInfo('http://openid.net/schema/account_verified', 1, true, 'accountVerified'));
        if ($axRequest) {
            $authRequest->addExtension($axRequest);
        }
        // For OpenID 2, use a Javascript form to send a POST request to the server.
        // Generate form markup and render it.
        $formHtml = $authRequest->htmlMarkup($trustRoot, $returnTo, false, array('id' => 'openid_message'));
        // Display an error if the form markup couldn't be generated; otherwise, render the HTML.
        if (\Auth_OpenID::isFailure($formHtml)) {
            throw new \Exception('Could not redirect to server: '.$formHtml->message.' in '.__METHOD__);
        }

        return $formHtml;
    }

    /**
     * Get response form OpenID Provider.
     *
     * @return array
     */
    public function response()
    {
        $consumer = $this->getConsumer();
        // Complete the authentication process using the server's response.
        $response = $consumer->complete($this->getReturnTo());
        // Check the response status.
        if ($response->status == Auth_OpenID_CANCEL) {
            // This means the authentication was cancelled.
            $status = 'error';
            $data = array('message' => 'Verification cancelled');
        } elseif ($response->status == Auth_OpenID_FAILURE) {
            // Authentication failed; display the error message.
            $status = 'error';
            $data = array('message' => "OpenID authentication failed: {$response->message}");
        } elseif ($response->status == Auth_OpenID_SUCCESS) {
            // This means the authentication succeeded; extract the
            // identity URL and Simple Registration data (if it was
            // returned).
            $identity = $this->escape($response->getDisplayIdentifier());
            $idAccount = intval(substr(strstr($identity, 'users/'), 6));
            if ($idAccount <= 0) {
                throw new \Exception('Invalid Account id');
            }
            $axResponse = \Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);

            if (!$axResponse) {
                throw new \Exception('Invalid Response');
            }
            $name = $axResponse->getSingle('http://openid.net/schema/namePerson');
            $email = $axResponse->getSingle('http://openid.net/schema/contact/email');
            $mobile = $axResponse->getSingle('http://openid.net/schema/contact/phone');
            $phoneNos = $axResponse->getSingle('http://openid.net/schema/contact/phone/additional');
            $gender = $axResponse->getSingle('http://openid.net/schema/gender');
            $dob = $axResponse->getSingle('http://openid.net/schema/birthDate');
            $bloodGroup = $axResponse->getSingle('http://openid.net/schema/blood_group');
            $street = $axResponse->getSingle('http://openid.net/schema/contact/postalAddress/line1');
            $locality = $axResponse->getSingle('http://openid.net/schema/contact/postalAddress/line2');
            $city = $axResponse->getSingle('http://openid.net/schema/contact/city');
            $state = $axResponse->getSingle('http://openid.net/schema/contact/state');
            $country = $axResponse->getSingle('http://openid.net/schema/contact/country');
            $pinCode = $axResponse->getSingle('http://openid.net/schema/contact/postalCode');
            $photoHash = $axResponse->getSingle('http://openid.net/schema/profile_picture_url');
            $timezone = $axResponse->getSingle('http://openid.net/schema/timezone');
            $referrer = $axResponse->getSingle('http://openid.net/schema/contact/referrer');
            $mobileVerified = $axResponse->getSingle('http://openid.net/schema/mobile_verified');
            $accountVerified = $axResponse->getSingle('http://openid.net/schema/account_verified');
            $status = 'success';
            $data = array(
                'AccountId' => $idAccount,
                'FullName' => $this->escape($name),
                'UserEmail' => $this->escape($email),
                'UserMobile' => $this->escape($mobile),
                'UserPhoneNos' => $this->escape($phoneNos),
                'Gender' => $this->escape($gender),
                'DOB' => $this->escape($dob),
                'BloodGroup' => $this->escape($bloodGroup),
                'Street' => $this->escape($street),
                'Locality' => $this->escape($locality),
                'City' => $this->escape($city),
                'State' => $this->escape($state),
                'Country' => $this->escape($country),
                'Pincode' => $this->escape($pinCode),
                'PhotoHash' => $this->escape($photoHash),
                'Timezone' => $this->escape($timezone),
                'Referrer' => $this->escape($referrer),
                'MobileVerified' => $this->escape($mobileVerified),
                'AccountVerified' => $this->escape($accountVerified),
            );
        }

        return array(
            'status' => $status,
            'data' => $data,
        );
    }

    /**
     * Get Consumer.
     *
     * @return Auth_OpenID_Consumer
     */
    protected function getConsumer()
    {
        $memcache = new \Memcached();
        $memcache->addServer($this->memcachedHost, $this->memcachedPort);
        $store = new Auth_OpenID_MemcachedStore($memcache);

        return new Auth_OpenID_Consumer($store);
    }

    /**
     * HTML escape helper.
     *
     * @param string $thing - text to html escape
     *
     * @return srting
     */
    protected function escape($thing)
    {
        return htmlentities($thing);
    }


    /**
     * @param null $intent
     * @param null $redirectTo
     *
     * @return string
     */
    private function getReturnTo($intent = null, $redirectTo = null)
    {
        return $this->fitDomain->getHost().'/api/uinf';
    }
}
