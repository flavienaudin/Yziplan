<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 04/10/2016
 * Time: 12:17
 */

namespace ATUserBundle\Controller;


use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class InitializePasswordController
 * @package ATUserBundle\Controller
 * @Route("/{_locale}/init-password", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class InitializePasswordController extends Controller
{

    /**
     * Initialise le mot de passe d'un utilisateur ne le connaissant pas car l'utilisateur a été créé via hwi-oAuth (ou par invitation)
     * @Route("/request", name="init_password_request")
     */
    public function requestPasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof AccountUser) {
            throw $this->createAccessDeniedException($this->get("translator")->trans("global.error.unauthorized_access"));
        }

        if ($user->isPasswordKnown()) {
            $this->addFlash('warning', $this->get("translator")->trans('profile.message.init_password.already_init'));
            return new RedirectResponse($this->generateUrl('fos_user_profile_show'));
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if (null === $user) {
            return $this->render('FOSUserBundle:Resetting:request.html.twig', array(
                'invalid_username' => $user->getUsername()
            ));
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->render('FOSUserBundle:Resetting:password_already_requested.html.twig');
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        /* Dispatch confirm event */
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }
        $this->get('fos_user.mailer')->sendInitializePasswordMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);


        /* Dispatch completed event */
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }
        return new RedirectResponse($this->generateUrl('init_password_check_email',
            array('email' => $this->getObfuscatedEmail($user))
        ));
    }

    /**
     * Tell the user to check his email provider
     * @Route("/check-email", name="init_password_check_email")
     */
    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('email');

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('fos_user_resetting_request'));
        }

        return $this->render('FOSUserBundle:Resetting:check_email.html.twig', array(
            'email' => $email,
        ));
    }

    /**
     * Initialize user password
     * @Route("/initialize/{token}", name="init_password_initialize", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param string  $token     *
     * @return Response
     */
    public function initializeAction(Request $request, $token)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('@ATUser/InitializePassword/initialize.html.twig', array(
            'token' => $token,
            'form' => $form->createView(),
        ));
    }


    /**
     * Get the truncated email displayed when requesting the initialization.     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param UserInterface $user     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }
}