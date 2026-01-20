<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\KeycloakClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class KeycloakAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{

    public function __construct(private ClientRegistry $clientRegistry,
                                private EntityManagerInterface $entityManager,
                                private RouterInterface $router)
    {}

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return Response
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/oauth/login', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * @param Request $request
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'oauth_check';
    }

    /**
     * @param Request $request
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak');
        $accessToken = $this->fetchAccessToken($client);
        $request->getSession()->set('oauth_token', $accessToken);
        $passport = new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var KeycloakResourceOwner $oauthUser */
                $oauthUser = $client->fetchUserFromToken($accessToken);
                $attributes = $oauthUser->toArray();
                $existingUser = $this->entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['keycloakId' => $oauthUser->getId()]);
                //si on trouve un utilisateur qui s'est deja connecté en Keycloak
                if ($existingUser) {
                    $existingUser->setDateDerniereConnexion(new \DateTime('Europe/Paris'));
                    $this->entityManager->persist($existingUser);
                    $this->entityManager->flush();
                    return $existingUser;
                }
                $email = $oauthUser->getEmail();
                $user = $this->entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);
                //si on a l'utilisateur en BDD mais qu'il ne s'est jamais authentifié en Keycloak
                if($user){
                    $user->setKeycloakId($oauthUser->getId());
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    return $user;
                }
                //si on ne trouve pas du tout l'utilisateur, on le crée
                $user = new User();
                $user->setKeycloakId($oauthUser->getId());
                $user->setEmail($email);
                $user->setPrenom($oauthUser->getFirstName()); //usual_forename
                $user->setNom($oauthUser->getLastName()); //uniquement pour test
                $user->setRoles(['ROLE_USER']);
                $user->setDatePremiereConnexion(new \DateTime('Europe/Paris'));
                $user->setDateDerniereConnexion(new \DateTime('Europe/Paris'));
                //$user->setNom($attributes['usual_name']); //à décommenter pour la pprod et prod
                //$user->setAffectation($attributes['main_department_number']); //à décommenter pour la pprod et prod
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $user;
            })
        );
        return $passport;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->router->generate('app_default');
        $request->getSession()->start();
        return new RedirectResponse($targetUrl);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response("Accès interdit", Response::HTTP_FORBIDDEN);
    }

    private function getUserInfo(OAuth2ClientInterface $oauthClient, AccessToken $accessToken): array
    {
        $keycloakUrl = $oauthClient->getOAuth2Provider()->authServerUrl;
        $keycloakRealm = $oauthClient->getOAuth2Provider()->realm;

        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $keycloakUrl . '/realms/' . $keycloakRealm . '/protocol/openid-connect/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken->getToken(),
            ],
        ]);

        // Convertit la réponse JSON en tableau PHP
        return $response->toArray();
    }
}
