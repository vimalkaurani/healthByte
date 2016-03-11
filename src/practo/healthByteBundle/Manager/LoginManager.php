<?php

/**
 * Created by PhpStorm.
 * User: harshitpahuja
 * Date: 9/1/15
 * Time: 12:45 PM.
 */
namespace practohealthByteBundle\Manager;

use Fit\ContentBundle\Constants\FitConstants;
use Fit\ContentBundle\Entity\Users;
use Fit\ContentBundle\Security\User\UserHelper;
use Fit\ContentBundle\FitDomain;
use GuzzleHttp\Client;
use Raven_Client;
use GuzzleHttp\Ring\Exception\ConnectException;

/**
 * Class LoginManager.
 */
class LoginManager extends BaseManager
{
    /**
     * @var \Fit\ContentBundle\Security\User\UserHelper
     */
    private $userHelper;
    /**
     * @var FitDomain
     */
    private $fitDomain;
    /**
     * @var
     */
    private $sentryDsn;
    /**
     * @var
     */
    private $fabricAuthToken;

    /**
     * @var
     */
    private $mixpanelSecretKey;

    /**
     * @param \Fit\ContentBundle\Security\User\UserHelper $userHelper
     * @param string                                      $sentryDsn
     * @param string                                      $fabricAuthToken
     * @param string                                      $mixpanelSecretKey
     */
    public function __construct(UserHelper $userHelper, $sentryDsn, $fabricAuthToken, $mixpanelSecretKey)
    {
        $this->userHelper = $userHelper;
        $this->sentryDsn = $sentryDsn;
        $this->fabricAuthToken = $fabricAuthToken;
        $this->mixpanelSecretKey = $mixpanelSecretKey;
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
     * @param int    $practoAccountId
     * @param array  $userDetails
     * @param string $apikey
     * @param null   $intent
     * @param null   $redirectTo
     *
     * @return string
     *
     * @throws \Exception
     */
    public function updateUserForLogin($practoAccountId, $userDetails, $apikey, $intent = null, $redirectTo = null)
    {
        $feedRedirectString = $redirectTo;
        if ((int) $practoAccountId === 0) {
            return $this->fitDomain->getHost('accounts');
        }
        $em = $this->helper->getEntitiesManager();
        $existingUserToken = $this->checkifUSerisValid($em, $practoAccountId);
        $existingUser = $em->getRepository('practohealthByteBundle:Users')->findOneBy(array('practoAccountId' => $practoAccountId, 'softDeleted' => 0));
        $userHelper = $this->userHelper;
        if (is_null($existingUser)) {
            // to create a new user
            $client = new Raven_Client(
                $this->sentryDsn,
                array(
                    // pass along the version of your application
                    'release' => '1.0.0',
                )
            );
            $client->captureMessage('user loop', 'line 55', 'info');
            $user = $userHelper->createUserForOpenAuth($userDetails);
            $fabricData = $this->getFabricData($userDetails['AccountId']);
            $user->setSpecialization($this->getUserSpecialization($userDetails['AccountId'], $fabricData));
            $userRole = $this->getRoleData($fabricData);
            $user->setUserRole($userRole);
            if (array_key_exists('id', $fabricData)) {
                $user->setFabricId($fabricData['id']);
            }
            if (array_key_exists('name', $fabricData)) {
                $user->setName($fabricData['name']);
            }
            if (array_key_exists('photos', $fabricData) && array_key_exists('0', $fabricData['photos']) && array_key_exists('photo_url', $fabricData['photos'][0])) {
                $user->setImageUrl($fabricData['photos'][0]['photo_url']);
            }
            $em->persist($user);
            $email = $user->getEmail();
            $e = $this->helper->getRepository(FitConstants::INVITE_DOCTORS_ENTITY);
            $doctor = $e->findOneBy(array('email' => $email, 'softDeleted' => 0));
            if (is_null($doctor)) {
                $userToken = $userHelper->addUserToken('Open-Id-Auth', $user, $apikey);
                $em->persist($userToken);
                if ($userRole == 'PATIENT') {
                    $redirectString = $this->fitDomain->getHost('www');
                } elseif ($userRole == 'ADMIN') {
                    $redirectString = $this->fitDomain->getHost().'/#!/login?token='.$apikey.'&uid='.$user->getPractoAccountId().'&role='.$userRole;
                } else {
                    $redirectString = $this->fitDomain->getHost().'/#!/invite?token='.$apikey.'&uid='.$user->getPractoAccountId().'&role='.$userRole;
                }
                $redirectString = $this->checkForIntent($redirectString, $intent, $feedRedirectString, $apikey, $user->getPractoAccountId());
            } else {
                $userToken = $userHelper->addUserToken('Open-Id-Auth', $user, $apikey);
                $em->persist($userToken);
                if ($userRole == 'PATIENT') {
                    $redirectString = $this->fitDomain->getHost('www');
                } else {
                    $redirectString = $this->fitDomain->getHost().'/#!/login?token='.$apikey.'&uid='.$user->getPractoAccountId().'&role='.$userRole;
                }
                $redirectString = $this->checkForIntent($redirectString, $intent, $feedRedirectString, $apikey, $user->getPractoAccountId());
            }
        } else {
            $email = $existingUser->getEmail();
            $e = $this->helper->getRepository(FitConstants::INVITE_DOCTORS_ENTITY);
            $doctor = $e->findOneBy(array('email' => $email, 'softDeleted' => 0));
            if (is_null($doctor)) {
                $userRole = $existingUser->getUserRole();
                if (!is_null($existingUserToken)) {
                    $apikey = $existingUserToken->getFitToken();
                    if ($userRole == 'PATIENT') {
                        $redirectString = $this->fitDomain->getHost('www');
                    } elseif ($userRole == 'ADMIN') {
                        $redirectString = $this->fitDomain->getHost().'/#!/login?token='.$apikey.'&uid='.$existingUser->getPractoAccountId().'&role='.$userRole;
                    } else {
                        $redirectString = $this->fitDomain->getHost().'/#!/invite?token='.$apikey.'&uid='.$existingUser->getPractoAccountId().'&role='.$userRole;
                    }
                    $redirectString = $this->checkForIntent($redirectString, $intent, $feedRedirectString, $apikey, $existingUser->getPractoAccountId());
                    $em->flush();
                } else {
                    //case when for an existing user token has expired
                    $userToken = $userHelper->addUserToken('Open-Id-Auth', $existingUser, $apikey);
                    $em->persist($userToken);
                    if ($userRole == 'PATIENT') {
                        $redirectString = $this->fitDomain->getHost('www');
                    } elseif ($userRole == 'ADMIN') {
                        $redirectString = $this->fitDomain->getHost().'/#!/login?token='.$apikey.'
                        &uid='.$existingUser->getPractoAccountId().'&role='.$userRole;
                    } else {
                        $redirectString = $this->fitDomain->getHost().'/#!/invite?token='.$apikey.'
                        &uid='.$existingUser->getPractoAccountId().'&role='.$userRole;
                    }
                    $redirectString = $this->checkForIntent($redirectString, $intent, $feedRedirectString, $apikey, $existingUser->getPractoAccountId());
                }
            } else {
                //to update in case changes in profile
                $userRole = $existingUser->getUserRole();
                if (!is_null($existingUserToken)) {
                    $apikey = $existingUserToken->getFitToken();
                    if ($userRole == 'PATIENT') {
                        $redirectString = $this->fitDomain->getHost('www');
                    } else {
                        $redirectString = $this->fitDomain->getHost().'/#!/login?token='.$apikey.'&uid='.$existingUser->getPractoAccountId().'&role='.$userRole;
                    }
                    $redirectString = $this->checkForIntent($redirectString, $intent, $feedRedirectString, $apikey, $existingUser->getPractoAccountId());
                    $em->flush();
                } else {
                    //case when for an existing user token has expired
                    $userToken = $userHelper->addUserToken('Open-Id-Auth', $existingUser, $apikey);
                    $em->persist($userToken);
                    if ($userRole == 'PATIENT') {
                        $redirectString = $this->fitDomain->getHost('www');
                    } else {
                        $redirectString = $this->fitDomain->getHost().'/#!/login?token='.$apikey.'&uid='.$existingUser->getPractoAccountId().'&role='.$userRole;
                    }
                    $redirectString = $this->checkForIntent($redirectString, $intent, $feedRedirectString, $apikey, $existingUser->getPractoAccountId());
                }
            }
        }

        $em->flush();

        return $redirectString;
    }

    /**
     * updateUserToken.
     */
    public function updateUserToken()
    {
    }

    /**
     * @param object $em
     * @param int    $practoAccId
     *
     * @return null
     */
    public function checkifUSerisValid($em, $practoAccId)
    {
        $userToken = $em->getRepository('FitContentBundle:UserToken')
            ->findOneBy(array('practoAccountId' => $practoAccId, 'softDeleted' => 0, 'tokenScope' => 'Open-Id-Auth'));
        if (!is_null($userToken)) {
            $createdAt = $userToken->getModifiedAt();
            $currentDate = new \DateTime('now');
            $diff = $currentDate->diff($createdAt);
            if (!($diff->h >= 2 || $diff->d > 1 || $diff->m > 1 || $diff->y > 1)) {
                //token is still valid
                return $userToken;
            }
            $userToken->setSoftDeleted(1);
            $em->persist($userToken);
            $em->flush();
        }

        return null;
    }

    /**
     * @param mixed $fabricData
     *
     * @return string
     */
    public function getRoleData($fabricData)
    {
        //access denied or ok status means it is a doctor profile
        if (!empty($fabricData)) {
            $userRole = 'DOCTOR';

            return $userRole;
        } else {
            return 'PATIENT';
        }
    }

    /**
     * @param int $practoAccountId
     *
     * @return array
     */
    public function getFabricData($practoAccountId)
    {
        $res = $this->getDataFromFabric($practoAccountId, 'object');
        if (is_object($res)  && $res->getStatusCode() === 200) {
            $responseArray = $res->json();

            return $responseArray;
        } else {
            return array();
        }
    }


    /**
     * @param int   $practoAccountId
     * @param mixed $res
     *
     * @return null|object|void
     */
    public function getUserSpecialization($practoAccountId, $res)
    {
        $client = new Raven_Client(
            $this->sentryDsn,
            array(
                // pass along the version of your application
                'release' => '1.0.0',
            )
        );
        $responseArray = array(
            'accountid' => $practoAccountId,
            'fabricresponse' => $res,
        );

        $client->captureMessage('user specialization', json_encode($responseArray), 'info');
        if (is_array($res)) {
            if (array_key_exists('specializations', $res)) {
                if (!isset($res['specializations'][0])) {
                    $client->captureMessage('user specialization blank', json_encode($res), 'info');

                    return;
                }
                $specializationArray = $res['specializations'][0];
                if (is_array($specializationArray)) {
                    $speciality = null;
                    $subspeciality = null;
                    if (array_key_exists('subspecialization', $specializationArray)) {
                        $specialization = $specializationArray['subspecialization']['subspecialization'];
                        if (trim($specialization) !== '') {
                            $rep = $this->helper->getRepository(FitConstants::SPECIALIZATION_ENTITY);
                            $subspeciality = $rep->findOneby(array('specialization' => $specialization));
                        }
                    }
                    if (array_key_exists('subspecialization', $specializationArray) && array_key_exists('speciality', $specializationArray['subspecialization'])) {
                        $specialityArray = $specializationArray['subspecialization']['speciality'];
                        if (array_key_exists('speciality', $specialityArray)) {
                            $specialization = $specialityArray['speciality'];
                            if (trim($specialization) !== '') {
                                $rep = $this->helper->getRepository(FitConstants::SPECIALIZATION_ENTITY);
                                $speciality = $rep->findOneby(array('specialization' => $specialization));
                            }
                        }
                    }
                    if ($speciality !== null) {
                        return $speciality;
                    } else {
                        return $subspeciality;
                    }
                }
            }
        } else {
            $client->captureMessage('specializationArray is not an array', json_encode($res), 'info');
        }
    }

    /**
     * @param int   $practoAccountId
     * @param array $returnType
     *
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
     */
    public function getDataFromFabric($practoAccountId, $returnType = 'json')
    {
        $xAuthToken = $this->fabricAuthToken;
        try {
            $client = new Client(
                array('base_url' => $this->fitDomain->getHost('www'),
                      'defaults' => array('headers' => array('X-AUTH-TOKEN' => $xAuthToken)), )
            );
            $urlString = '/health/api/doctors/'.$practoAccountId.'.json?is_account_id=true&is_doctor=true';
            $res = $client->get($urlString, ['exceptions' => false, 'timeout' => 10, 'connect_timeout' => 10]);
            if ($returnType == 'json') {
                $isValidJson = $this->isJson($res->getBody());
                if ($isValidJson) {
                    return $res->json();
                } else {
                    //--comments--// $this->logger->info('Invalid JSON Received');
                    return json_encode(array());
                }
            } else {
                return $res;
            }
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
        } catch (ConnectException $e) {
        }
    }

    /**
     * @param string $redirectString
     * @param string $intent
     * @param string $feedRedirectString
     * @param null   $apikey
     * @param null   $practoAccountId
     *
     * @return string
     */
    public function checkForIntent($redirectString, $intent, $feedRedirectString, $apikey = null, $practoAccountId = null)
    {
        if ($intent === 'feed') {
            $redirectString = $feedRedirectString.'?apikey='.$apikey.'&practoAccountId='.$practoAccountId;
        } elseif ($intent === 'compose' || $intent === 'comment' || $intent === 'topics' || $intent === 'sarticle' || $intent === 'imgGallery' || $intent === 'dashboard') {
            $redirectString = $redirectString.'&link='.$feedRedirectString.'&intent='.$intent;
        }

        return $redirectString;
    }


    /**
     * @param array $requestParams
     *
     * @return null|object
     */
    public function signupMobileDoctor($requestParams)
    {
            $user = $this->helper->getRepository(FitConstants::USERS_ENTITY_NAME)->findOneBy(array(
                'practoAccountId' => $requestParams['practoAccountId'],
                'softDeleted' => 0,
            ));

            return $user;
    }


    /**
     * @param Users $user
     * @param int   $practoAccountId
     *
     * @return Users
     */
    public function updateFabricData(Users $user, $practoAccountId)
    {
        $fabricData = $this->getFabricData($practoAccountId);
        if (array_key_exists('name', $fabricData)) {
            $user->setName($fabricData['name']);
        }
        if (array_key_exists('photos', $fabricData) && array_key_exists('0', $fabricData['photos']) && array_key_exists('photo_url', $fabricData['photos'][0])) {
            $user->setImageUrl($fabricData['photos'][0]['photo_url']);
        }
        if (array_key_exists('id', $fabricData)) {
            $user->setFabricId($fabricData['id']);
        }
        $user->setSpecialization($this->getUserSpecialization($practoAccountId, $fabricData));
        $user->setUserRole($this->getRoleData($fabricData));

        return $user;
    }
}
