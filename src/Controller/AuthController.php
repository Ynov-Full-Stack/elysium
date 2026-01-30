<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    private $client;
    private $supabaseUrl;
    private $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL');
        $this->supabaseKey = $_ENV['SUPABASE_KEY'] ?? getenv('SUPABASE_KEY');

        $this->client = new Client([
            'base_uri' => rtrim($this->supabaseUrl, '/') . '/',
            'timeout' => 5.0,
        ]);
    }

    #[Route('/login', name: 'app_login_supabase', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('pages/auth/login.html.twig');
    }





    #[Route('/register', name: 'app_register_supabase', methods: ['GET', 'POST'])]
    public function register(Request $request, SessionInterface $session): Response
    {
        if ($session->get('user')) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $displayName = $request->request->get('display_name');
            $password = $request->request->get('password');

            if (!$email || !$password || !$displayName) {
                $this->addFlash('error', 'Veuillez remplir tous les champs.');
            } else {
                try {
                    $response = $this->client->post('/auth/v1/signup', [
                        'headers' => [
                            'apikey' => $this->supabaseKey,
                            'Authorization' => 'Bearer ' . $this->supabaseKey,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'email' => $email,
                            'data' => [
                                'display_name' => $displayName,
                            ],
                            'password' => $password,
                        ],
                    ]);

                    $body = json_decode($response->getBody(), true);
                    $accessToken = $body['access_token'] ?? null;
                    $user = $body['user'] ?? null;

                    if ($accessToken) {
                        $session->set('access_token', $accessToken);
                        $session->set('user', $user);
                        $this->addFlash('success', 'Votre compte a bien été créé !');
                        return $this->redirectToRoute('app_login_supabase');
                    }
                } catch (RequestException $e) {
                    $errorBody = $e->hasResponse() ? (string)$e->getResponse()->getBody() : $e->getMessage();
                    $decodedError = json_decode($errorBody, true);
                    $this->addFlash('error', 'Inscription a échoué');
                }
            }
        }
        return $this->render('pages/auth/register.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout_supabase')]
    public function logout(SessionInterface $session): Response
    {
        $token = $session->get('access_token');

        if ($token) {
            try {
                $this->client->post('/auth/v1/logout', [
                    'headers' => [
                        'apikey' => $this->supabaseKey,
                        'Authorization' => 'Bearer ' . $token,
                    ]
                ]);
            } catch (\Exception $e) {
            }
        }

        $session->clear();
        return $this->redirectToRoute('app_home');
    }
}
