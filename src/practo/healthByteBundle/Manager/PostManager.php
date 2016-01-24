<?php
/**
 * Created by PhpStorm.
 * User: vimalkaurani
 * Date: 22/01/16
 * Time: 1:11 PM
 */

namespace practo\healthByteBundle\Manager;


use practo\healthByteBundle\Entity\post;

class PostManager extends BaseManager
{


    public function getPostObject($urlParams = null)
    {
        $em = $this->helper->getEntitiesManager();
        $qb = $em->createQueryBuilder();
        $qb->select('u')
            ->from('practohealthByteBundle:post', 'u');

        foreach($urlParams as $key => $val) {
            $qb->andWhere('u.'.$key.' LIKE :'.$key);
            $qb ->setParameter($key, '%'.$val.'%');
        }

        $data = $qb->getQuery()->getArrayResult();

        return $data;
    }

    public function addPostObject($urlParams = null){
        $post = new post();
        /*$post->setContent($urlParams['content']) ;
        $post->setTitle($urlParams['title']);
        $post->setImgurl($urlParams['imgurl']);*/

        foreach($urlParams as $key => $val) {
            $param = ucfirst($key);
            $methodName = 'set'.$param;
            $post->$methodName($urlParams[$key]);
       //     echo $methodName, $urlParams[$key] ;

        }

        $this->helper->persist($post, true);

        $id = $post->getId();
        $this->helper->flush();
        print_r($id);
        return $this->getPostObject(array('id'=>$id));
    }

    public function patchPostObject($id, $urlParams = null){

        $post = $this->helper->loadById($id, 'practohealthByteBundle:post');
        #var_dump($user);
        //echo "<pre>"; print_r($user); die;
        if (null === $post) {
            throw new \Exception('post with this id does not exist');
        }
        /*$user->setName($urlParams['name']);
        $user->setEmail($urlParams['email']);*/
        foreach($urlParams as $key => $val) {
            $param = ucfirst(strtolower($key));
            $methodName = 'set'.$param;
            $post->$methodName($urlParams[$key]);
        }
        $this->helper->persist($post, true);
        $id = $post->getId();
        $this->helper->flush();
        #print_r($id);
        return $this->getPostObject(array('id'=>$id));
    }
    public function deletePostObject($id){
        $em = $this->helper->getEntitiesManager();
        $qb = $em->createQueryBuilder();
        $qb->delete()
            ->from('practohealthByteBundle:post', 'u');
        $qb->andWhere('u.id =' .$id);
        $qb->getQuery()->getArrayResult();
    }
}