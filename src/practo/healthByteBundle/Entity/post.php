<?php

namespace practo\healthByteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity
 */
class post
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="practo_account_id", type="integer")
     */
    private $practoAccountId;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="imgurl", type="text")
     */
    private $imgurl;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datePublished", type="datetime")
     */
    private $datePublished;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateWritten", type="datetime")
     */
    private $dateWritten;

    /**
     * @var string
     *
     * @ORM\Column(name="published_draft", type="string", length=255)
     */
    private $publishedDraft;

    /**
     * @var integer
     *
     * @ORM\Column(name="soft_deleted", type="integer")
     */
    private $softDeleted;


    /**
     * Get practoAccountId
     *
     * @return integer 
     */
    public function getPractoAccountId()
    {
        return $this->id;
    }

    /**
     * Set practoAccountId
     *
     * @param string $practoAccountId
     * @return post
     */
    public function setPractoAccountId($practoAccountId)
    {
        $this->practoAccountId = $practoAccountId;

        return $this;
    }

    /**
     * Get softDeleted
     *
     * @return integer 
     */
    public function getSoftDeleted()
    {
        return $this->softDeleted;
    }

    /**
     * Set softDeleted
     *
     * @param integer $softDeleted
     * @return post
     */
    public function setSoftDeleted($softDeleted)
    {
        $this->softDeleted = $softDeleted;

        return $this;
    }

    /**
     * Get userid
     *
     * @return string 
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set imgurl
     *
     * @param string $imgurl
     * @return post
     */
    public function setImgurl($imgurl)
    {
        $this->imgurl = $imgurl;

        return $this;
    }

    /**
     * Get imgurl
     *
     * @return string 
     */
    public function getImgurl()
    {
        return $this->imgurl;
    }

    /**
     * Set datePublished
     *
     * @param \DateTime $datePublished
     * @return post
     */
    public function setDatePublished($datePublished)
    {
        $this->datePublished = new \DateTime($datePublished);

        return $this;
    }

    /**
     * Get datePublished
     *
     * @return \DateTime 
     */
    public function getDatePublished()
    {
        return $this->datePublished;
    }

    /**
     * Set dateWritten
     *
     * @param \DateTime $dateWritten
     * @return post
     */
    public function setDateWritten($dateWritten)
    {
        $this->dateWritten = new \DateTime($dateWritten);

        return $this;
    }

    /**
     * Get dateWritten
     *
     * @return \DateTime 
     */
    public function getDateWritten()
    {
        return $this->dateWritten;
    }

    /**
     * Set publishedDraft
     *
     * @param string $publishedDraft
     * @return post
     */
    public function setPublishedDraft($publishedDraft)
    {
        $this->publishedDraft = $publishedDraft;

        return $this;
    }

    /**
     * Get publishedDraft
     *
     * @return string 
     */
    public function getPublishedDraft()
    {
        return $this->publishedDraft;
    }
}
