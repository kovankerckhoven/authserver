<?php

namespace User\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use User\Form\DeleteAuthorizedAppType;
use App\Entity\User;
use User\Form\ChangePasswordType;

class ProfileController extends Controller
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return $this->getUser();
    }

    /**
     * @Template
     * Internal action, not exposed in a route
     */
    public function removeAuthorizedAppAction($appId)
    {
        return $this->createForm(new DeleteAuthorizedAppType(), array('id'=>$appId));
    }

    public function deleteAuthorizedAppAction(Request $request)
    {
        $form = $this->createForm(new DeleteAuthorizedAppType());

        $form->handleRequest($request);

        if($form->isValid()) {
            $appId = $form->get('id')->getData();
            $client = $this->getDoctrine()
                ->getRepository('AppBundle:OAuth\Client')
                ->find($appId);

            if($client && ($user = $this->getUser()) && $user instanceof User) {
                $user->removeAuthorizedApplication($client);
                $this->getDoctrine()->getRepository('AppBundle:User')->update($user);
                $this->get('braincrafted_bootstrap.flash')->success('Authorized application has been removed');

                return $this->redirectToProfile();
            }
        }

        $this->get('braincrafted_bootstrap.flash')->error('Error removing authorized application');

        return $this->redirectToProfile();
    }

    /**
     * @Template
     */
    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(new ChangePasswordType());

        $form->handleRequest($request);

        if($form->isValid()) {
            $user->setPassword($form->get('password')->getData());
            $this->getDoctrine()->getRepository('AppBundle:User')->update($user);
            $this->get('braincrafted_bootstrap.flash')->success('Password has been changed successfully');

            return $this->redirectToProfile();
        }

        return $form;
    }

    private function redirectToProfile()
    {
        return $this->redirect($this->generateUrl('user_profile'));
    }
}
