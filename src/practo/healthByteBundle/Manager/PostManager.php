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


    function tokenTruncate($string, $width) {
        
        return substr($string,0,$width).'...';
        }

    
    

    public function getPostObject($urlParams = null)
    {
        $em = $this->helper->getEntitiesManager();
        $qb = $em->createQueryBuilder();
        $qb->select('u')
            ->from('practohealthByteBundle:post', 'u');

        $inArray = array('id');
        $likeArray = array('title');
        foreach($urlParams as $key => $val) {
            if(in_array($key, $inArray)) {
                

                $qb->andWhere('u.'.$key.' IN (:'.$key.')');
                $qb ->setParameter($key, $val);
            } elseif (in_array($key, $likeArray)) {
                $qb->andWhere('u.'.$key.' LIKE :'.$key);
                $qb ->setParameter($key, '%'.$val.'%');
            }

            

        }
       $qb->orderBy('u.dateWritten', 'DESC');

        $data = $qb->getQuery()->getArrayResult();

         foreach ($data as $key => $value) {
            if(strlen($value['content']) > 80){
                $data[$key]['exp']= $this->tokenTruncate($value['content'],80);
            }
        
             else {
                 $data[$key]['exp']=$value['content'];
             }
         }

        return $data;
    }

    public function addPostObject($urlParams = null){
        $post = new post();

        foreach($urlParams as $key => $val) {
            $param = ucfirst($key);
            $methodName = 'set'.$param;
            if($urlParams[$key] != null && $urlParams[$key] != ''){
                $post->$methodName($urlParams[$key]);
            }
            else {
                return array('error_message' => 'something is wrong');
            }
        }
        $this->helper->persist($post, true);
        $id = $post->getId();
        $this->helper->flush();
        //print_r($id);
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
