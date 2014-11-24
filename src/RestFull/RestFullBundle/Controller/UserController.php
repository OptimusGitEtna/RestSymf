<?php

namespace RestFull\RestFullBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RestFull\RestFullBundle\Entity\User;
use RestFull\RestFullBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * User controller.
 *
 * @Route("/")
 */
class UserController extends Controller
{

    /**
     * return a status 200.
     * @Route("/user", name="home_bis")
     * @Route("/users", name="homes_bis")
     * @Route("/user/", name="home")
     * @Route("/users/", name="homes")
     * @Method({"GET", "POST"})
     */
    public function homeAction(Request $oRequest)
    {

        $oEm = $this->getDoctrine()->getManager();
        $oUserRepository = $this->getDoctrine()->getManager()->getRepository("RestFullRestFullBundle:User");
        $aUserFromJson  = json_decode($oRequest->getContent(), true);
        
        $response = new Response('Content', 200, array('content-type' => 'text/html'));
        
        if ($oRequest->isMethod('post')) {
          
            // Recuperation du dernier id de la base.
            $aLastUserId = $oUserRepository->getLastUserId();
            $iLastUserId = (int) $aLastUserId[1];
            
            $oUser = new User();
            $oUser->setId($iLastUserId+1);
            $oUser ->setLastname($aUserFromJson['lastname']);
            $oUser ->setFirstname($aUserFromJson['firstname']);
            $oUser ->setEmail($aUserFromJson['email']);
    
            $oUser ->setRole($aUserFromJson['role']);
            $oUser ->setPassword($aUserFromJson['password']);
            
            $bUserExists = $this->isUserMailExist($oUser->getEmail());
            if (true == $bUserExists) 
            {
                $array = array(
                   'status' => 401,
                   'message' => "User email already exists in database");
                
                $response = new Response(json_encode($array), 401);
                $response->headers->set('Content-Type', 'application/json');
                
                return $response;
            }
            
            $oEm->persist($oUser);
            $oEm->flush($oUser);
            
            $array = array(
               'status' => 201,
               'message' => "User created");
        
            $response = new Response(json_encode($array), 201);
            $response->headers->set('Content-Type', 'application/json');
        }
        
        return $response;
    }
    
    
    private function isUserMailExist($sUserMail)
    {
        
        $oEm = $this->getDoctrine()->getManager();
        $aUser = $oEm->getRepository("RestFullRestFullBundle:User")
                ->findBy(array('email' => $sUserMail));
        
        if (isset($aUser) && !empty($aUser)) 
        {
            return true;
        }
        else
        {
            return false;
        }        
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/user/{id}", name="user_show_bis")
     * @Route("/users/{id}", name="users_show_bis")
     * @Route("/user/{id}/", name="user_show")
     * @Route("/users/{id}/", name="users_show")
     * @Method("GET")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);

        if (!$entity) {

            $array = array(
                'status' => 404,
                'message' => "User not found",);
            $response = new Response(json_encode($array), 404);
            $response->headers->set('Content-Type', 'application/json');
        }
        else if ($entity->getRole() == "admin") {

            $array = array(
                'status' => 401,
                'message' => "Unauthorized access",);
            $response = new Response(json_encode($array), 401);
            $response->headers->set('Content-Type', 'application/json');
        }
        else {
            $response = new JsonResponse();
            $response->setData(array(
                'id' => $entity->getId(),
                'lastname' => $entity->getLastName(),
                'firstname' => $entity->getfirstName(),
                'email' => $entity->getEmail(),
                'role' => $entity->getRole()
            ));
        }

        return $response;
    }
    
    /**
     * Edits an existing User entity.
     *
     * @Route("/user/{id}", name="user_update_bis")
     * @Route("/users/{id}", name="users_update_bis")
     * @Route("/user/{id}/", name="user_update")
     * @Route("/users/{id}/", name="users_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $oUserRepository = $this->getDoctrine()->getManager()
                ->getRepository("RestFullRestFullBundle:User");

        $aUserFromJson  = json_decode($request->getContent(), true);
        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);
        
        if (!$entity) {
            
            $array = array(
               'status' => 404,
               'message' => "User not found");
            $response = new Response(json_encode($array), 404);
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
        $this->updateUserAboutJsonStream($entity, $aUserFromJson);
        
        $array = array(
               'status' => 202,
               'message' => "User updated");
        
        $response = new Response(json_encode($array), 202);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    private function updateUserAboutJsonStream($entity, $aUserFromJson) 
    {
        /*foreach ($aUserFromJson as $sIndexAttrib => $sValueAttrib)
                {
                    $attrib = ucfirst( $sIndexAttrib );

                    $methode = "set".$attrib."('$sValueAttrib')";
                    //$entity->set."$attrib".($sValueAttrib);
                }*/
        foreach ($aUserFromJson as $sIndexAttrib => $sValueAttrib)
        {
            if ("firstname" == $sIndexAttrib) 
            {
                $entity->setFirstName($sValueAttrib);
            }
            if ("lastname" == $sIndexAttrib) 
            {
                $entity->setLastName($sValueAttrib);
            }
            if ("email" == $sIndexAttrib) 
            {
                $entity->setEmail($sValueAttrib);
            }
            if ("role" == $sIndexAttrib) 
            {
                $entity->setRole($sValueAttrib);
            }
            if ("password" == $sIndexAttrib) 
            {
                $entity->setPassword($sValueAttrib);
            }
        }
        
        $oEm = $this->getDoctrine()->getManager();
        $oEm->persist($entity);
        $oEm->flush();
    }
    
    /**
     * Deletes a User entity.
     *
     * @Route("/user/{id}", name="user_delete_bis")
     * @Route("/users/{id}", name="users_delete_bis")
     * @Route("/user/{id}/", name="user_delete")
     * @Route("/users/{id}/", name="users_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        //die('methode delete');die;
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }
        
        $em->remove($entity);
        $em->flush();
        
        $array = array(
               'status' => 202,
               'message' => "User deleted");
        
        $response = new Response(json_encode($array), 202);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
