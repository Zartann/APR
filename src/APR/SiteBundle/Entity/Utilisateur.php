<?php

namespace APR\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * APR\SiteBundle\Entity\Utilisateur
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="hruid", columns={"hruid"})})
 * @ORM\Entity(repositoryClass="APR\SiteBundle\Entity\UtilisateurRepository")
 */
class Utilisateur
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
     * @var string $hruid
     *
     * @ORM\Column(name="hruid", type="string", length=255, nullable=true)
     */
    private $hruid;

    /**
     * @var string $nom
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string $surnom
     *
     * @ORM\Column(name="surnom", type="string", length=255, nullable=true)
     */
    private $surnom;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var boolean $complet
     * @ORM\Column(name="complet", type="boolean", nullable=false)
     */
    private $complet;

    /**
     * @ORM\OneToOne(targetEntity="APR\SiteBundle\Entity\Creneau", inversedBy="inscrit")
     * @ORM\JoinColumn(nullable=true)
     */
    private $creneau = null;

    public function __construct($h, $n, $s=null, $e=null) {
        $this->hruid = $h;
        $this->nom = $n;
        $this->surnom = $s;
        $this->email = $e;
        $this->complet = false;
    }

    static public function get($id) {
        return $this->getDoctrine()->getEntityManager()->getRepository("APRSiteBundle:Utilisateur")->find($id);
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
     * Set hruid
     *
     * @param string $hruid
     * @return Utilisateur
     */
    public function setHruid($hruid)
    {
        $this->hruid = $hruid;
    
        return $this;
    }

    /**
     * Get hruid
     *
     * @return string 
     */
    public function getHruid()
    {
        return $this->hruid;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Utilisateur
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    
        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set surnom
     *
     * @param string $surnom
     * @return Utilisateur
     */
    public function setSurnom($surnom)
    {
        $this->surnom = $surnom;
    
        return $this;
    }

    /**
     * Get surnom
     *
     * @return string 
     */
    public function getSurnom()
    {
        return $this->surnom;
    }

    /**
     * Set creneau
     *
     * @param APR\SiteBundle\Entity\Creneau $creneau
     * @return Utilisateur
     */
    public function setCreneau(\APR\SiteBundle\Entity\Creneau $creneau = null)
    {
        $this->creneau = $creneau;
        if($creneau != null)
            $creneau->setInscrit($this);
    
        return $this;
    }

    /**
     * Get creneau
     *
     * @return APR\SiteBundle\Entity\Creneau 
     */
    public function getCreneau()
    {
        return $this->creneau;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Utilisateur
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function generateJson() {
        $u = new UtilisateurJson($this->id, $this->hruid, $this->nom, $this->surnom, $this->email);
        $u->id = $this->id;
        $u->hruid = $this->hruid;
        $u->name = $this->nom;
        $u->nickname = $this->surnom;
        $u->email = $this->email;
        return $u;
    }
    
    /**
     * Set complet
     *
     * @param boolean $complet
     * @return Utilisateur
     */
    public function setComplet($complet)
    {
        $this->complet = $complet;
    
        return $this;
    }

    /**
     * Get complet
     *
     * @return boolean 
     */
    public function getComplet()
    {
        return $this->complet;
    }
}

class UtilisateurJson
{
    public $id;
    public $hruid;
    public $name;
    public $nickname;
    public $email;
}
