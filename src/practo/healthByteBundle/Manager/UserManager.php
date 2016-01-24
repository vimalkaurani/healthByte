<?php

/**
 * Created by PhpStorm.
 * User: vimalkaurani
 * Date: 20/01/16
 * Time: 12:17 PM
 */

namespace practo\healthByteBundle\Manager;

use practo\healthByteBundle\Entity\User;


class UserManager extends BaseManager
{

    /**
     * @param null $urlParams
     *
     * @return mixed
     */
    public function getUserObject($urlParams = null)
    {
        $em = $this->helper->getEntitiesManager();
        $qb = $em->createQueryBuilder();
        $qb->select('u')
            ->from('practohealthByteBundle:User', 'u');

        foreach($urlParams as $key => $val) {
            $qb->andWhere('u.'.$key.' LIKE :'.$key);
            $qb ->setParameter($key, '%'.$val.'%');
        }

        $data = $qb->getQuery()->getArrayResult();

        return $data;
    }

    public function addUserObject($urlParams = null){
        $user = new user();
        $user->setEmail($urlParams['email']);
        $user->setName($urlParams['name']);
        $this->helper->persist($user, true);

        $id = $user->getId();
        $this->helper->flush();
        #print_r($id);
        return $this->getUserObject(array('id'=>$id));
    }

    public function patchUserObject($id, $urlParams = null){

        $user = $this->helper->loadById($id, 'practohealthByteBundle:User');
        #var_dump($user);
        //echo "<pre>"; print_r($user); die;
        if (null === $user) {
            throw new \Exception('user with this id does not exist');
        }
        /*$user->setName($urlParams['name']);
        $user->setEmail($urlParams['email']);*/
        foreach($urlParams as $key => $val) {
            $param = ucfirst(strtolower($key));
            $methodName = 'set'.$param;
            $user->$methodName($urlParams[$key]);
        }
        $this->helper->persist($user, true);

        $id = $user->getId();
        $this->helper->flush();
        #print_r($id);
        return $this->getUserObject(array('id'=>$id));
    }

    public function deleteUserObject($id){
        $em = $this->helper->getEntitiesManager();
        $qb = $em->createQueryBuilder();
        $qb->delete()
            ->from('practohealthByteBundle:User', 'u');


            $qb->andWhere('u.id =' .$id);



        $qb->getQuery()->getArrayResult();
    }

}