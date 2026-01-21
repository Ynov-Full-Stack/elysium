<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Security\KeycloakService;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\KeycloakClient;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('pages/home/index.html.twig');
    }

    #[Route('/oauth/login', name: 'oauth_login')]
    public function oauthLogin(ClientRegistry $clientRegistry): RedirectResponse
    {
        /** @var KeycloakClient $client */
        $client = $clientRegistry->getClient('keycloak');
        return $client->redirect(['openid']);
    }

    #[Route('/oauth/callback', name: 'oauth_check')]
    public function oauthCheck(){}

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/oauth/logout', name: 'oauth_logout')]
    public function oauthLogout(
        Request $request,
        ClientRegistry $clientRegistry,
        KeycloakService $keycloakService,
        Security $security,
        #[Autowire('%oauth_secret%')] string $secret,
        #[Autowire('%oauth_client%')] string $clientId,
    ): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $client = $clientRegistry->getClient('keycloak');
        $session = $request->getSession();
        /** @var AccessToken $accessToken */
        $accessToken = $session->get('oauth_token');
        $keycloakService->logout($client, $clientId, $secret, $accessToken->getToken(), $accessToken->getRefreshToken());
        $session->invalidate();
        $response = $security->logout(false);
        return $this->redirectToRoute('app_deconnexion');
    }

    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function deconnexion(): Response
    {
        return $this->render('default/deconnexion.html.twig');
    }
}
