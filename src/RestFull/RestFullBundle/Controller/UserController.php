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
     *
     * @Route("/user/", name="home")
     * @Method({"GET", "POST"})
     * @Template("RestFullRestFullBundle:User:index.html.twig")
     */
    public function homeAction(Request $oRequest)
    {

        $em = $this->getDoctrine()->getManager();

        if ($oRequest->isMethod('post')) {

            $aUsers = $this->getDoctrine()->getManager()
                ->getRepository("RestFullRestFullBundle:User")
                ->findAll();

            $aNbUsers = count($aUsers);
            $oUser = new User();
            $oUser->setId($aNbUsers+1);
            $oUser->setlastname($oRequest->request->get('lastname'));
            $oUser->setFirstname($oRequest->request->get('lastname'));
            $oUser->setEmail($oRequest->request->get('email'));
            $oUser->setRole($oRequest->request->get('password'));
            $oUser->setPassword($oRequest->request->get('role'));

            $em->persist($oUser);
            $em->flush();
        }


        //die('fin method homeAction');

        $response = new Response('Content', 200, array('content-type' => 'text/html'));
        return $response;


    }

    /**
     * affiche le formulaire test pour envoi de post
     *
     * @Route("/user/post", name="post_test")
     * @Method("GET")
     */
    public function testPostAction()
    {

        //die('methode post');
        return $this->render("RestFullRestFullBundle:User:test_post.html.twig");
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
     * @Route("/user/{id}", name="user_update")
     * @Method("PUT")
     * @Template("RestFullRestFullBundle:User:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $em->flush();
            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a User entity.
     *
     * @Route("/user/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RestFullRestFullBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
