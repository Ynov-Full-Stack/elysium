<?php

namespace App\Security;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SupabaseAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private Client $client;
    private string $supabaseUrl;
    private string $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL');
        $this->supabaseKey = $_ENV['SUPABASE_KEY'] ?? getenv('SUPABASE_KEY');

        $this->client = new Client([
            'base_uri' => rtrim($this->supabaseUrl, '/') . '/',
            'timeout' => 5.0,
        ]);
    }

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');

        // 🔥 Appel Supabase avant de créer le UserBadge
        $userData = $this->fetchSupabaseUser($email, $password);
        if (!$userData) {
            throw new AuthenticationException('Identifiants invalides');
        }

        return new Passport(
            new UserBadge($email, fn() => new SupabaseUser([
                'id' => $userData['id'],
                'email' => $userData['email'],
                'access_token' => $userData['access_token'],
            ])),
            new CustomCredentials(
                fn($credentials, UserInterface $user) => true, // déjà vérifié par fetchSupabaseUser
                ['password' => $password]
            )
        );
    }

    /**
     * Appel l'API Supabase pour vérifier les identifiants et récupérer les données utilisateur.
     */
    private function fetchSupabaseUser(string $email, string $password): ?array
    {
        try {
            $response = $this->client->post('/auth/v1/token?grant_type=password', [
                'headers' => [
                    'apikey' => $this->supabaseKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (!isset($body['access_token'], $body['user'])) {
                return null;
            }

            // Ajoute l'access_token à l'utilisateur pour un futur usage si besoin
            $body['user']['access_token'] = $body['access_token'];

            return $body['user'];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        // Redirection après login
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        return new RedirectResponse($targetPath ?: '/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        $request->getSession()->getFlashBag()->add('error', 'Identifiants invalides');
        return null;
    }
}
