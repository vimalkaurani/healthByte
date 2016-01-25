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

        foreach($urlParams as $key => $val) {
            $param = ucfirst($key);
            $methodName = 'set'.$param;
            $post->$methodName($urlParams[$key]);

        }

        $this->helper->persist($post, true);

        $id = $post->getId();
        $this->helper->flush();
        print_r($id);
        return $this->getPostObject(array('id'=>$id));
    }

    public function patchPostObject($id, $urlParams = null){

        $post = $this->helper->loadById($id, 'practohealthByteBundle:post');
        if (null === $post) {
            throw new \Exception('post with this id does not exist');
        }

        foreach($urlParams as $key => $val) {
            $param = ucfirst($key);
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
            ->from('practohealthByteBundle:post', 'p');
        $qb->andWhere('p.id =' .$id);
        $qb->getQuery()->getArrayResult();
    }
}