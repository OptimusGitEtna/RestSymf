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
     * Lists all User entities.
     *
     * @Route("/user/list", name="user")
     * @Route("/users/list", name="users_list")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('RestFullRestFullBundle:User')->findAll();

        return array(
            'entities' => $entities,
        );
    }

     /**
     * return a status 200.
     * @Route("/user/", name="home")
     * @Route("/users/", name="homes")
     * @Method({"GET", "POST"})
     * @Template("RestFullRestFullBundle:User:index.html.twig")
     */
    public function homeAction(Request $oRequest)
    {
    
        $oEm = $this->getDoctrine()->getManager();
        $oUserRepository = $this->getDoctrine()->getManager()->getRepository("RestFullRestFullBundle:User");
        $aUserFromJson  = json_decode($oRequest->getContent(), true);
        
        if ($oRequest->isMethod('post')) {
          
            /* $aUsers = $this->getDoctrine()->getManager()
                ->getRepository("RestFullRestFullBundle:User")
                ->findBy(array('id'=>'DESC'));*/
            
            //var_dump($aUserFromJson['lastname']);
            //$iNbUsers = count($aUsers);
            
            // Recuperation du dernier di de la base.
            $aLastUserId = $oUserRepository->getLastUserId();
            $iLastUserId = (int) $aLastUserId[1];
            
            $oUser = new User();
            /*$oUser->setId($aNbUsers+1);
                    $oUser->setlastname($oRequest->request->get('lastname'));
                    $oUser->setFirstname($oRequest->request->get('lastname'));
                    $oUser->setEmail($oRequest->request->get('email'));
                    $oUser->setRole($oRequest->request->get('role'));
                    $oUser->setPassword($oRequest->request->get('password'));*/
            $oUser->setId($iLastUserId+1);
            $oUser ->setLastname($aUserFromJson['lastname']);
            $oUser ->setFirstname($aUserFromJson['firstname']);
            $oUser ->setEmail($aUserFromJson['email']);
            $oUser ->setRole($aUserFromJson['role']);
            $oUser ->setPassword($aUserFromJson['password']);
	                
            $oEm->persist($oUser);
            $oEm->flush($oUser);
        }
	die('created');
        $response = new Response('Content', 200, array('content-type' => 'text/html'));
        return $response;
    }

    /**
     * affiche le formulaire test pour envoi de post
     *
     * @Route("/user/form/{iIdUser}", name="post_test")
     * @Route("/users/form/{iIdUser}", name="post_test")
     * @Method({"GET", "PUT"})
     */
    public function testPostAction($iIdUser)
    {


        $form = $this->createDeleteForm($iIdUser);
        return $this->render("RestFullRestFullBundle:User:test_post.html.twig", array(

            'sTypeAction' => "post",
            'form_update' => $form->createView(),
            ));
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/user/", name="user_create")
     * @Method("POST")
     * @Template("RestFullRestFullBundle:User: .html.twig")
     */
    public function createAction(Request $request)
    {

        $oEm = $this->getDoctrine()->getManager();

        //var_dump("<PRE>",$request->request);die;
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repo = $oEm->getRepository("RestFullRestFullBundle:User");

            //$repo->getNumberOfUser();
            //$u = $oEm->getRepository("RestFullRestFullBundle:User")->findAll();
            //$r = count($u);
            //$entity->setId(110);
            //var_dump("<PRE>",$entity);die;

            $em->persist($entity);
            $em->flush($entity);
            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/user/new", name="user_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {

        $entity = new User();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            //'formCustom' => $formCustom->createView(),
        );
    }

    /**
     * Finds and displays a User entity.
     *
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
                'message' => "not found",);
            $response = new Response(json_encode($array), 404);
            $response->headers->set('Content-Type', 'application/json');
            //throw $this->createNotFoundException('Unable to find User entity.');
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
     * Displays a form to edit an existing User entity.
     *
     * @Route("/user/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }





     /**
     * Edits an existing User entity.
     *
     * @Route("/user/{id}/", name="user_update")
     * @Route("/users/{id}/", name="user_update")
     * @Method("PUT")
     * @Template("RestFullRestFullBundle:User:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $oUserRepository = $this->getDoctrine()->getManager()->getRepository("RestFullRestFullBundle:User");

        $aUserFromJson  = json_decode($request->getContent(), true);
        //var_dump("<PRE>",$aUserFromJson);die;
        
        // verification et bind des donnees utilisateur du flux json avec celui de la bdd.
        //var_dump($aUserFromJson);die;
        
        //$oUserRepository->getUserByAttrib($aUserFromJson);
        
        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);
        
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        
        $this->updateUserAboutJsonStream($entity, $aUserFromJson);
        /*
                $deleteForm = $this->createDeleteForm($id);
                $editForm = $this->createEditForm($entity);
                $editForm->handleRequest($request);

                if ($editForm->isValid()) {

                    $em->flush();
                    return $this->redirect($this->generateUrl('home', array('id' => $id)));
                }

                return array(
                    'entity'      => $entity,
                    'edit_form'   => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                );*/
        
    }
    
    private function updateUserAboutJsonStream($entity, $aUserFromJson) 
    {
        //var_dump("<PRE>",$entity, $aUserFromJson);die;
       // var_dump($entity);
        /*foreach ($aUserFromJson as $sIndexAttrib => $sValueAttrib)
        {
            $attrib = ucfirst( $sIndexAttrib );
            
            $methode = "set".$attrib."('$sValueAttrib')";
            //var_dump($methode);die;
            var_dump($entity->$methode);die;
            //$entity->set."$attrib".($sValueAttrib);
        }*/
        foreach ($aUserFromJson as $sIndexAttrib => $sValueAttrib)
        {
            if ("firstname" == $sIndexAttrib) 
            {
                //die('firstname ok');
                $entity->setFirstName($sValueAttrib);
            }
            if ("lastname" == $sIndexAttrib) 
            {
                //die('lastname ok');
                $entity->setLastName($sValueAttrib);
            }
            if ("email" == $sIndexAttrib) 
            {
                //die('lastname ok');
                $entity->setEmail($sValueAttrib);
            }
            if ("role" == $sIndexAttrib) 
            {
                //die('role ok');
                $entity->setRole($sValueAttrib);
            }
            if ("password" == $sIndexAttrib) 
            {
                //die('password ok');
                $entity->setPassword($sValueAttrib);
            }
        }
        
        $oEm = $this->getDoctrine()->getManager();
        
        $oEm->persist($entity);
        $oEm->flush();die;        
    }

     /**
     * Deletes a User entity.
     *
     * @Route("/user/{id}", name="user_delete")
     * @Route("/users/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
	
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);

        $em->remove($entity);
        $em->flush();
        die('done');
    }
}
