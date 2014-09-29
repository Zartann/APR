<?php

namespace APR\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * APR\SiteBundle\Entity\Creneau
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="APR\SiteBundle\Entity\CreneauRepository")
 */
class Creneau
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime $debut
     *
     * @ORM\Column(name="debut", type="datetime")
     */
    private $debut;

    /**
     * @var \DateTime $fin
     *
     * @ORM\Column(name="fin", type="datetime")
     */
    private $fin;

    /**
     * @ORM\OneToOne(targetEntity="APR\SiteBundle\Entity\Utilisateur", mappedBy="creneau")
     */
    private $inscrit;

    public function __construct($debut, $fin) {
        $this->debut = $debut;
        $this->fin = $fin;
        $this->inscrit = null;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set debut
     *
     * @param \DateTime $debut
     * @return Creneau
     */
    public function setDebut($debut)
    {
        $this->debut = $debut;
    
        return $this;
    }

    /**
     * Get debut
     *
     * @return \DateTime 
     */
    public function getDebut()
    {
        return $this->debut;
    }

    /**
     * Set fin
     *
     * @param \DateTime $fin
     * @return Creneau
     */
    public function setFin($fin)
    {
        $this->fin = $fin;
    
        return $this;
    }

    /**
     * Get fin
     *
     * @return \DateTime 
     */
    public function getFin()
    {
        return $this->fin;
    }

    /**
     * Set inscrit
     *
     * @param APR\SiteBundle\Entity\Utilisateur $inscrit
     * @return Creneau
     */
    public function setInscrit(\APR\SiteBundle\Entity\Utilisateur $inscrit = null)
    {
        $this->inscrit = $inscrit;
    
        return $this;
    }

    /**
     * Get inscrit
     *
     * @return APR\SiteBundle\Entity\Utilisateur 
     */
    public function getInscrit()
    {
        return $this->inscrit;
    }

    public function generateJson() {
        $j = new CreneauJson();
        $j->id = $this->id;
        $j->start = $this->debut->getTimestamp();
        $j->end = $this->fin->getTimestamp();
        if($this->inscrit == null)
            $j->available = true;
        else
            $j->available = false;
        return $j;
    }
    public function generateJsonAdmin() {
        $j = new CreneauJsonAdmin();
        $j->id = $this->id;
        $j->start = $this->debut->getTimestamp();
        $j->end = $this->fin->getTimestamp();
        $j->user = ($this->inscrit == null)? null : $this->inscrit->getId();
        return $j;
    }
    public function isPast() {
        return ($this->debut->format("U") - time())<0;
    }
}

class CreneauJson
{
    public $id;
    public $start;
    public $end;
    public $available;
}
class CreneauJsonAdmin
{
    public $id;
    public $start;
    public $end;
    public $user;
}
