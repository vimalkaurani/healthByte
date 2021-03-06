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
}
