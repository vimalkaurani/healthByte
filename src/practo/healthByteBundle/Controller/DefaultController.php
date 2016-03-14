<?php

namespace practo\healthByteBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
class DefaultController extends Controller
{
    public function indexAction()
    {
        return (array("arr"=>'rahul'));

    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function uloginAction(Request $request)
    {
        
        $requestParams = $request->query->all();
        $openId = $this->get('healthByte.openid');
        $requestForm = $openId->requestForm();

       	return new Response($requestForm);
    }
    
}
