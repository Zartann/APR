<?php

namespace APR\SiteBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use APR\SiteBundle\Entity\Utilisateur;

class HomeController extends Controller
{
    private $frkzKey = 's9%32k&5u7#Ip4';

    public function indexAction()
    {
        $user = $this->getLoggedUser();
        if($user == false)
          return $this->redirect($this->generateUrl("apr_site_login"));
        
        $request = $this->get('request');
        if($request->getMethod() == 'POST') {
            //Pour activer la modification de pseudo, il faut retirer la ligne suivante et modifier la vue.
            //return registerAction(); 
            $em = $this->getDoctrine()->getEntityManager();
            $user->setSurnom($request->request->get('surnom'));
            $em->persist($user);
            $user->setComplet(true);
            $em->flush();
        } 
        return $this->render('APRSiteBundle:Default:home.html.twig', array("user" => $user, "admin" => $this->get('session')->get('admin')));
    }

    public function adminAction()
    {
        // if(!$this->get('session')->get('admin')) {
            // header("HTTP/1.1 403 Access Denied"); exit();
        // }
        return $this->render('APRSiteBundle:Default:admin.html.twig');
    }

    public function loginAction()
    {
        $champs = json_encode(array('names', 'rights', 'email', 'promo'));
        $time = time();
        $site = 'http://' . $_SERVER['SERVER_NAME'] . $this->generateUrl("apr_site_logged");
        $hash = md5($time . $site . $this->frkzKey . $champs);
        return $this->redirect("https://www.frankiz.net/remote?timestamp=" . time() . "&site=" . $site . "&hash=" . $hash . "&request=" . $champs);
    }

    public function loggedAction()
    {
        $request = $this->get('request');
        $session = $this->get('session');
        $time = $request->query->get('timestamp');
        $response = $request->query->get('response');
        $hash = $request->query->get('hash');
        if((abs($time - time()) > 180) || md5($time . $this->frkzKey . $response) != $hash)
            return $this->redirect($this->generateUrl("apr_site_login"));
        $response = json_decode($response, true);
        if($response['promo'] != 2012)
            exit('Désolé, mais ce site est réservé à la promo 2010');
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('APRSiteBundle:Utilisateur')->findOneBy(array('hruid'=>$response['hruid']));
        if($user == null) {
            $user = new Utilisateur($response['hruid'], $response['firstname'] . ' ' . $response['lastname'], $response['nickname'], $response['email']);
            $user->setSurnom($response['nickname']);
        }
        $em->persist($user);
        $user->setEmail($response['email']);
        $em->flush();
        $session->set('user', $user->getId()); 
        $r = $response['rights'];
        $admin = array_key_exists("albumpromo", $r) && in_array("admin", $r['albumpromo']);
        $session->set('admin', $admin);
        if(! $user->getComplet())
            return $this->redirect($this->generateUrl("apr_site_register"));
        if($admin)
            return $this->redirect($this->generateUrl("apr_site_adminhome"));
        return $this->redirect($this->generateUrl("apr_site_home"));
    }

    public function logoutAction()
    {
        $this->get('session')->clear();
        return $this->render('APRSiteBundle:Default:logout.html.twig', array('admin' => $this->get('session')->get('admin')));
    }

    public function registerAction()
    {
        $user = $this->getLoggedUser();
        if(!$user)
            die("error");
        return $this->render('APRSiteBundle:Default:register.html.twig', array('user' => $user, 'admin' => $this->get('session')->get('admin')));
    }
    protected function getLoggedUser()
    {
        $session = $this->get('session');
        $rep = $this->getDoctrine()->getManager()->getRepository("APRSiteBundle:Utilisateur");
        $id = $session->get('user');
        if(isset($id))
            return $rep->find($id);
        else
            return false;
    }
}
