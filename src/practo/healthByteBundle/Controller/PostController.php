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
use Fit\ContentBundle\FitDomain;



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
        $sess = new Session();
        $sess->set('_openid_consumer_last_token', 'abc');
        $details = $openId->response();
        $user['practoAccountId'] = $details['data']['AccountId'];
        $user['name'] = $details['data']['FullName'];
        $user['email'] = $details['data']['UserEmail'];
        $this->get('fit.user_manager')->addUserObject($user);
        $redirectString = '/#!/login?uid='.$user['practoAccountId'].'&name='.$user['name'];
        return $this->redirect($redirectString);
    }
}
