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

    public function tokenTruncate($string, $width) {       
               
        return substr($string,0,$width).'...';        
    }

    public function getPostObject($urlParams = null)
    {
        $em = $this->helper->getEntitiesManager();
        $qb = $em->createQueryBuilder();
        $qb->select('u')
            ->from('practohealthByteBundle:post', 'u');

        $inArray = array('id','practoAccountId');
        $likeArray = array('title','publishedDraft');
        foreach($urlParams as $key => $val) {
            if(in_array($key, $inArray)) {
                $qb->andWhere('u.'.$key.' IN (:'.$key.')');
                $qb ->setParameter($key, $val);
            } elseif (in_array($key, $likeArray)) {
                $qb->andWhere('u.'.$key.' LIKE :'.$key);
                $qb ->setParameter($key, '%'.$val.'%');
            } 
            

        }
        if(array_key_exists('deleted', $urlParams) && $urlParams['deleted'] == 'true') {
            $qb-> andWhere('u.softDeleted = 1');
        } elseif(!array_key_exists('id', $urlParams)) {
            $qb-> andWhere('u.softDeleted = 0');
        } 

        // $count=$qb->getQuery()->getSingleScalarResult();
        // echo $count;
        $qb->orderBy('u.dateWritten', 'DESC');
        if(array_key_exists('pageno', $urlParams)){
           $qb->setMaxResults(10);
            $qb->setFirstResult(10 * ($urlParams['pageno']-1));
        }

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb);
        $totalRows = count($paginator);
        $data = $qb->getQuery()->getArrayResult();

         foreach ($data as $key => $value) {
            if(strlen($value['content']) > 50){

                $data[$key]['exp']= $this->tokenTruncate($value['content'],50);
            }
        
             else {
                 $data[$key]['exp']=$value['content'];
             }

            if(strlen($value['title']) > 40){
                
                $data[$key]['ttl']= $this->tokenTruncate($value['title'],40);
            }
        
             else {
                 $data[$key]['ttl']=$value['title'];
             }
         }

         $result['data'] = $data;
        $result['totalCount'] = $totalRows;
        return $result;
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
                return array('error_message' => 'Please submit title and content');
            }
        }
        $this->helper->persist($post, true);
        $id = $post->getId();
        $this->helper->flush();
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
            $post = $this->helper->loadById($id, 'practohealthByteBundle:post');
            $post->setSoftDeleted(1);
            $this->helper->persist($post, true);
            $this->helper->flush();

        // $em = $this->helper->getEntitiesManager();
        // $qb = $em->createQueryBuilder();
        // $qb->delete()
        //     ->from('practohealthByteBundle:post', 'p');
        // $qb->andWhere('p.id =' .$id);
        // $qb->getQuery()->getArrayResult();
    }
}
