<?php
/**
 * Created by PhpStorm.
 * User: vimalkaurani
 * Date: 22/01/16
 * Time: 1:10 PM
 */

namespace practo\healthByteBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use practo\healthByteBundle\Controller\BasehealthByteController;
use Symfony\Component\HttpFoundation\Session\Session;



class PostController extends BasehealthByteController
{
    public function getPostsAction(Request $request){

        $urlParams = $request->query->all();
        
        $data = $this->get('fit.post_manager')->getPostObject($urlParams);
        //var_dump($data);
        return $data;
    }

    public function getPostAction($id, Request $request){

        $urlParams = $request->query->all();
        $urlParams['id'] = $id;
        $data = $this->get('fit.post_manager')->getPostObject($urlParams);
        return $data;
    }

    public function postPostAction(Request $request){

        $urlParams = $request->request->all();
        $data = $this->get('fit.post_manager')->addPostObject($urlParams);
        return $data;
    }

    public function patchPostAction($id, Request $request){
        $urlParams = $request->request->all();
        $data = $this->get('fit.post_manager')->patchPostObject($id, $urlParams);
        return $data;
    }

    public function deletePostAction($id){


        $data = $this->get('fit.post_manager')->deletePostObject($id);
        return $data;
    }

    public function uinfAction(Request $request)
    {
        $openId = $this->get('healthByte.openid');
        // $logger = $this->get('logger');
         $sess = new Session();
         $sess->set('_openid_consumer_last_token', 'abc');
         $randomByte = openssl_random_pseudo_bytes(20);
         $apikey = bin2hex($randomByte);
        
         $details = $openId->response();

        $response = json_encode($details);
        //die("Ss2");

         // $logger->info('user data: '.json_encode($details));
         // $userDetails = $details['data'];
         // $practoAccountId = $userDetails['AccountId'];
         // $requestParams = $request->query->all();
        // if (array_key_exists('intent', $requestParams) && array_key_exists('redirectTo', $requestParams)) {
        //     $redirectString = $this->get('fit.login_manager')->updateUserForLogin($practoAccountId, $userDetails, $apikey, $requestParams['intent'], $requestParams['redirectTo']);
        // } else {
            
        //$redirectString = $this->get('fit.login_manager')->updateUserForLogin($practoAccountId, $userDetails, $apikey);
        // }

        // return $this->redirect($redirectString);
        return $details;
    }
}
