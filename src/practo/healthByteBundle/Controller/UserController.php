<?php
/**
 * Created by PhpStorm.
 * User: vimalkaurani
 * Date: 20/01/16
 * Time: 12:19 PM
 */

namespace practo\healthByteBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use practo\healthByteBundle\Controller\BasehealthByteController;
/**
 * Class UserController.
 */
class UserController extends BasehealthByteController
{
    public function getUserAction(Request $request)
    {
        $urlParams = $request->query->all();

        $data = $this->get('fit.user_manager')->getUserObject($urlParams);

        return $data;

    }

    public function postUserAction(Request $request){

        $urlParams = $request->request->all();
        $data = $this->get('fit.user_manager')->addUserObject($urlParams);
        return $data;
    }

    public function patchUserAction($id, Request $request){
        $urlParams = $request->request->all();
        $data = $this->get('fit.user_manager')->patchUserObject($id, $urlParams);

        return $data;

    }

    public function deleteUserAction($id, Request $request){
        $urlParams = $request->query->all();
        $data = $this->get('fit.user_manager')->deleteUserObject($id);


        return $data;
    }


}