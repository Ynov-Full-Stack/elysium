<?php

namespace App\Security;

use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use Symfony\Component\HttpClient\HttpClient;

class KeycloakService
{
    /**
     * @param OAuth2ClientInterface $oauthClient
     * @param string $clientId
     * @param string $secret
     * @param string $accessToken
     * @param string $refreshToken
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function logout(OAuth2ClientInterface $oauthClient, $clientId, $secret, $accessToken, $refreshToken)
    {
        $keycloakUrl = $oauthClient->getOAuth2Provider()->authServerUrl;
        $keycloakRealm = $oauthClient->getOAuth2Provider()->realm;
        $httpClient = HttpClient::create();

        $response = $httpClient->request('POST', $keycloakUrl . '/realms/' . $keycloakRealm . '/protocol/openid-connect/logout', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                "Content-Type" => "application/x-www-form-urlencoded",
            ],
            'body' => [
                'client_id' => $clientId,
                'client_secret' => $secret,
                'refresh_token' => $refreshToken,
            ]
        ]);
    }
}
