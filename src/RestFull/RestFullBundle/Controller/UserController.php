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
            if (isset($aUserFromJson['lastname'])) 
                $oUser ->setLastname($aUserFromJson['lastname']);
            if (isset($aUserFromJson['firstname'])) 
                $oUser ->setFirstname($aUserFromJson['firstname']);
            if (isset($aUserFromJson['email'])) 
                $oUser ->setEmail($aUserFromJson['email']);
            if (isset($aUserFromJson['role'])) 
                $oUser ->setRole($aUserFromJson['role']);
            if (isset($aUserFromJson['password'])) 
                $oUser ->setPassword($aUserFromJson['password']);

            $bUserExists = $this->isUserMailExist($oUser->getEmail());
            $bDataUserConform = $this->dataUserIsConformToCreate($oUser);
            $bCheckParam = $this->checkParams($aUserFromJson);
            
            if (true == $bUserExists) 
            {
                $array = array(
                   'status' => 401,
                   'message' => "User email already exists in database");
                
                $response = new Response(json_encode($array), 401);
                $response->headers->set('Content-Type', 'application/json');
                
                return $response;
            }
            if (false == $bDataUserConform)
            {
                $array = array(
                   'status' => 403,
                   'message' => "Parameters missing for create user");
                
                $response = new Response(json_encode($array), 403);
                $response->headers->set('Content-Type', 'application/json');
                
                return $response;
            }
            
            if (false == $bCheckParam)
            {
                $array = array(
                   'status' => 403,
                   'message' => "Parameters wrong");
                
                $response = new Response(json_encode($array), 403);
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
    
    
    private function checkParams($aUserFromJson)
    {
        $aRightIndex = ['lastname', 'firstname', 'email', 'role', 'password'];
        foreach ($aUserFromJson as $sKey => $sData) 
        {
            if (!in_array($sKey, $aRightIndex))
            {
                return false;
            }
        }
        
        return true;
    }
    
     private function dataUserIsConformToCreate($oUser)
    {
        $oEm = $this->getDoctrine()->getManager();
        $bDataisConform = true;
        
        if (null == $oUser->getLastName()) 
        {   
            $bDataisConform = false;
        }
        if (null == $oUser->getFirstname()) 
        {
            $bDataisConform = false;
        }
        if (null == $oUser->getEmail()) 
        {
            $bDataisConform = false;
        }
        if (null == $oUser->getPassword()) 
        {
            $bDataisConform = false;
        }
        if (null == $oUser->getRole()) 
        {
            $bDataisConform = false;
        }

        return $bDataisConform;
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
     * @Method({"GET", "POST"})
     */
    public function showAction(Request $oRequest,$id)
    {
         if ($oRequest->isMethod('post'))
        {
            $array = array(
                'status' => 405,
                'message' => "Post no authorised",);
            $response = new Response(json_encode($array), 405);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        
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
                'password' => $entity->getPassword(),
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
     * @Route("/user/{id}", name="user_update_b")
     * @Route("/users/{id}", name="users_update_b")
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
        
        if (!$entity) 
        {
            
            $array = array(
               'status' => 404,
               'message' => "User not found");
            $response = new Response(json_encode($array), 404);
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        die('marche pas');
        if (isset($aUserFromJson['email']) && true == $this->isUserMailExist($aUserFromJson['email'])) 
        {
            $array = array(
               'status' => 401,
               'message' => "User email already exists in database");

            $response = new Response(json_encode($array), 401);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        $this->updateUserAboutJsonStream($entity, $aUserFromJson);
        
        $array = array(
               'status' => 200,
               'message' => "User updated");
        
        $response = new Response(json_encode($array), 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    private function updateUserAboutJsonStream($entity, $aUserFromJson) 
    {
             
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
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);
        
        if (!$entity) {
            
            $array = array(
               'status' => 404,
               'message' => "User not found");
            $response = new Response(json_encode($array), 404);
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
        $em->remove($entity);
        $em->flush();
        
        $array = array(
               'status' => 200,
               'message' => "User deleted");
        
        $response = new Response(json_encode($array), 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
