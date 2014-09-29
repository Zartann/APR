<?php
namespace APR\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use APR\SiteBundle\Entity\Creneau;
use APR\SiteBundle\Entity\Utilisateur;

class jsonController extends Controller
{
    public function checkLogged() {
        if(!$this->get('session')->get('user')) {
            header("HTTP/1.1 403 Access Denied"); exit();
        }
    }

    public function listAction()
    {
        $this->checkLogged();
        $user = $this->getLoggedUser();
        return new Response(json_encode($this->prepare($user)));
    }
    public function reserverAction($id)
    {
        $this->checkLogged();
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->getLoggedUser();
        $creneau = $em->getRepository('APRSiteBundle:Creneau')->find($id);
        if($creneau->isPast())
            $reussi = false;
        else
            $reussi = $this->reserverCreneau($user, $creneau);
        return new Response(json_encode(array("success" => $reussi)));
    }
    public function annulerAction()
    {
        $this->checkLogged();
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->getLoggedUser();
        $user->setCreneau(null);
        $em->persist($user);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    protected function prepare($user)
    {
        $id = ($user->getCreneau() != null) ? $user->getCreneau()->getId() : null;
        return array("user" => $user->getNom(), "id" => $id ,"creneaux" => $this->listAllCreneauxJson());
    }
    protected function getLoggedUser()
    {
        $session = $this->get('session');
        $rep = $this->getDoctrine()->getEntityManager()->getRepository("APRSiteBundle:Utilisateur");
        return $rep->find($session->get('user'));
    }
    protected function listAllCreneaux() {
        $rep = $this->getDoctrine()->getEntityManager()->getRepository("APRSiteBundle:Creneau");
        return $rep->findAll();
    }
    private function listAllCreneauxJson()
    {
        $creneaux = $this->listAllCreneaux();
        $creneaux_json = array();
        foreach($creneaux as $c) {
            $creneaux_json[] = $c->generateJson();
        }
        return $creneaux_json;
    }

    protected function reserverCreneau($user, $creneau) {
        try {
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($user);
        $user->setCreneau($creneau);
        $em->flush(); 
        }
        catch (\Doctrine\DBAL\DBALException $e) {
            if($e->getPrevious()->getCode() == 23000)
                return false;
            else
                throw $e;
        }
        return true;
    }
}
