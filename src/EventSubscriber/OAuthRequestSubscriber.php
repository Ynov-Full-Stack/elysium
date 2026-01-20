<?php

namespace App\EventSubscriber;


use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OAuthRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ClientRegistry $clientRegistry,
        private LoggerInterface $logger
    ){
    }

    /**
     * @return array[]|\array[][]|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $request = $event->getRequest();
        if($request->getPathInfo() !== "/oauth/logout") {
            /** @var AccessToken $accessToken */
            $accessToken = $session->get('oauth_token');

            if($accessToken && $accessToken->hasExpired()){
                $client = $this->clientRegistry->getClient('keycloak');
                try{
                    /** @var RefreshToken $refreshToken */
                    $refreshToken = $accessToken->getRefreshToken();

                    if($this->getExpFromToken($refreshToken) < new \DateTime()){
                        $this->logger->debug("AccesToken ET RefreshToken Expired. Redirigé vers une reconnexion ");

                        if($request->getPathInfo() !== "/oauth/logout"
                            && $request->getPathInfo() !== '/oauth/login'
                            && $request->getPathInfo() !== '/deconnexion'
                            && !str_contains($event->getRequest()->getPathInfo(), '/_wdt/')
                        ){
                            $url = $this->urlGenerator->generate('oauth_login');
                            $event->setResponse(new RedirectResponse($url));
                        }
                    } else {
                        $this->logger->debug("AccesToken à renouveller avec le RefreshToken");
                        $accessToken = $client->refreshAccessToken($accessToken->getRefreshToken());
                        $session->set('oauth_token', $accessToken);
                    }

                }catch (\Exception $e){
                    /**
                     * Cas inactivité
                     * Quand la session MDC est KO, l'accessToken + refresh n'existent plus --> Erreur "Token is not active"
                     * On check qu'on ne soit pas déjà en phase de logout ou de login
                     * Si c'est pas le cas, on force une reconnexion
                     */
                    $this->logger->debug('Echec du renouvellement de l\'access token avec le refreshToken. Redirigé vers une reconnexion');
                    $this->reconnection($event);
                }
            } elseif(!$accessToken){
                //si pas accessToken alors je redirige sur /secure/login
                $this->reconnection($event);
            }
        }
    }

    private function reconnection(RequestEvent $event): void {
        $request = $event->getRequest();
        if ($request->getPathInfo() !== '/oauth/logout' && $request->getPathInfo() !== '/oauth/login' && $request->getPathInfo() !== '/deconnexion') {
            $url = $this->urlGenerator->generate('oauth_login');
            $event->setResponse(new RedirectResponse($url));
        }
    }

    private function getExpFromToken(string $token): \DateTime {
        $tokenParts = explode('.', $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload);

        return (new \DateTime())->setTimestamp($jwtPayload->exp);
    }
}