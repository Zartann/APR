<?php
namespace APR\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use APR\SiteBundle\Entity\Creneau;
use APR\SiteBundle\Entity\Utilisateur;

class jsonAdminController extends jsonController {
    public function checkAdmin() {
        // if(!$this->get('session')->get('admin')) {
            // header("HTTP/1.1 403 Access Denied"); exit();
        // }
    }

    public function listAction() {
        $this->checkAdmin();
        return new Response(json_encode(array("creneaux" => $this->listAllCreneauxJson(), "users" => $this->listAllUsersJson())));
    }
    public function creerCreneauAction($debut, $fin) {
        $em = $this->getDoctrine()->getEntityManager();
        $debut = self::DateFromTS($debut);
        $fin = self::DateFromTS($fin);
        if($this->searchEvent($debut, $fin))
            return new Response(json_encode(array("success" => false)));
        $c = new Creneau($debut, $fin);
        $em->persist($c);
        $em->flush();

        return new Response(json_encode(array("success" => true, "id" => $c->getId())));
    }
    
    public function modifCreneauAction($id, $debut, $fin) {
        $this->checkAdmin();
        $em = $this->getDoctrine()->getEntityManager();
        $debut = self::DateFromTS($debut);
        $fin = self::DateFromTS($fin);
        $c = $em->getRepository('APRSiteBundle:Creneau')->find($id);
        if($this->searchEvent($debut, $fin, $c))
            return new Response(json_encode(array("success" => false)));
        if($c == null)
            return new Response(json_encode(array("success" => false)));
        $em->persist($c);
        $c->setDebut($debut);
        $c->setFin($fin);
        $em->flush();

        return new Response(json_encode(array("success" => true)));
    }

    public function supprCreneauAction($id) {
        $this->checkAdmin();
        $em = $this->getDoctrine()->getEntityManager();
        $c = $em->getRepository('APRSiteBundle:Creneau')->find($id);
        if($c == null)
            return new Response(json_encode(array("success" => false)));
        if($c->getInscrit()) {
            $em->persist($c->getInscrit());
            $c->getInscrit()->setCreneau(null);
        }
        $em->remove($c);
        $em->flush();

        return new Response(json_encode(array("success" => true)));
    }
    
    public function assignAction($userid, $creneauid) {
        $this->checkAdmin();
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('APRSiteBundle:Utilisateur')->find($userid);
        $creneau = $em->getRepository('APRSiteBundle:Creneau')->find($creneauid);
        if($user == null || $creneau == null)
            return new Response(json_encode(array("success" => false)));
        return new Response(json_encode(array("success" => $this->reserverCreneau($user, $creneau))));
    }

    public function creerUserAction() {
        $this->checkAdmin();
        $em = $this->getDoctrine()->getEntityManager();
        $post = $this->get('request')->request;
        $user = new Utilisateur(NULL, $post->get('name'), NULL, $post->get('email'));
        $em->persist($user);
        $em->flush();
        return new Response(json_encode(array("success"=>true, "id"=>$user->getId()))); 
    }
    
    public function modifUserAction($id) {
        $this->checkAdmin();
        $em = $this->getDoctrine()->getEntityManager();
        $post = $this->get('request')->request;
        $user = $em->getRepository('APRSiteBundle:Utilisateur')->find($id);
        if($user == null || $user->getHruid() != null)
            return new Response(json_encode(array("success"=>false)));
        $em->persist($user);
        $user->setNom($post->get('name'));
        $user->setEmail($post->get('email'));
        $em->flush();
        return new Response(json_encode(array("success"=>true))); 
    }

    public function supprUserAction($id) {
        $this->checkAdmin();
        $em = $this->getDoctrine()->getEntityManager();
        $c = $em->getRepository('APRSiteBundle:Utilisateur')->find($id);
        if($c == null)
            return new Response(json_encode(array("success" => false)));
        $em->remove($c);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    static function DateFromTS($TS) {
        $date = \DateTime::createFromFormat('U', $TS);
        $date->setTimeZone(new \DateTimeZone('Europe/Paris'));
        return $date;
    }

    private function listAllCreneauxJson() {
        $creneaux = $this->listAllCreneaux();
        $creneaux_json = array();
        foreach($creneaux as $c) {
            $creneaux_json[] = $c->generateJsonAdmin();
        }
        return $creneaux_json;
    }

    private function listAllUsersJson() {
        $rep = $this->getDoctrine()->getEntityManager()->getRepository("APRSiteBundle:Utilisateur");
        $users = $rep->findAll();
        $users_json = array();
        foreach ($users as $u) {
            $users_json[] = $u->generateJson();
        }
        return $users_json;
    }

    private function searchEvent($debut, $fin, $me = null) {
        $em = $this->getDoctrine()->getEntityManager();
        $rep = $em->getRepository('APRSiteBundle:Utilisateur');
        if($me == null)
            $query = $em->createQuery('SELECT COUNT(c) FROM APRSiteBundle:Creneau c WHERE (c.fin > :debut AND c.fin <= :fin) OR (c.debut < :fin AND c.fin >= :fin) OR (c.debut = :debut AND c.fin = :fin)');
        else {
            $query = $em->createQuery('SELECT COUNT(c) FROM APRSiteBundle:Creneau c WHERE ((c.fin > :debut AND c.fin <= :fin) OR (c.debut < :fin AND c.fin >= :fin) OR (c.debut = :debut AND c.fin = :fin)) AND c != :me');
        $query->setParameter('me', $me);
        }
        $query->setParameter('debut', $debut);
        $query->setParameter('fin', $fin);
        $result = $query->getSingleResult();
        var_dump($result);
        return ($result[1] > 0);
    }   
}
