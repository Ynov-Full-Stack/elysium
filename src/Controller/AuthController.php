<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AuthController extends AbstractController
{
    private Client $client;
    private string $supabaseUrl;
    private string $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?? '';
        $this->supabaseKey = $_ENV['SUPABASE_KEY'] ?? getenv('SUPABASE_KEY') ?? '';

        $this->client = new Client([
            'base_uri' => rtrim($this->supabaseUrl, '/') . '/',
            'timeout'  => 5.0,
        ]);
    }

    #[Route('/login', name: 'app_login_supabase', methods: ['GET', 'POST'])]
    public function login(Request $request, SessionInterface $session): Response
    {
        // Si l'utilisateur est déjà connecté, redirige vers la page d'accueil
        if ($session->get('user')) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));

            if (!$email) {
                $this->addFlash('error', 'Veuillez saisir votre email.');
                return $this->redirectToRoute('app_login_supabase');
            }

            try {
                // Envoi du Magic Link via l'API Supabase
                $this->client->post('/auth/v1/magiclink', [
                    'headers' => [
                        'apikey'        => $this->supabaseKey,
                        'Authorization' => 'Bearer ' . $this->supabaseKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'email' => $email,
                        'redirect_to' => $this->generateUrl(
                            'app_magic_login_callback', // callback Symfony
                            [],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                    ],
                ]);

                $this->addFlash('success', 'Magic link envoyé ! Vérifiez votre boîte mail.');
                return $this->redirectToRoute('app_login_supabase');

            } catch (RequestException $e) {
                $errorBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
                $this->addFlash('error', 'Impossible d’envoyer le magic link.');

                // Décommenter en dev pour voir l'erreur exacte
                // dd($errorBody);

                return $this->redirectToRoute('app_login_supabase');
            }
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
            $email       = trim((string) $request->request->get('email'));
            $displayName = trim((string) $request->request->get('display_name'));

            if (!$email || !$displayName) {
                $this->addFlash('error', 'Veuillez remplir tous les champs.');
                return $this->redirectToRoute('app_register_supabase');
            }

            try {
                // Inscription + magic link, avec metadata utilisateur
                $this->client->post('/auth/v1/magiclink', [
                    'headers' => [
                        'apikey'        => $this->supabaseKey,
                        'Authorization' => 'Bearer ' . $this->supabaseKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'email' => $email,
                        'redirect_to' => $this->generateUrl('app_magic_login_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'data' => [
                            'display_name' => $displayName ?? null,
                        ],
                    ],
                ]);

                $this->addFlash('success', 'Magic link envoyé ! Validez votre inscription via l’email.');
                return $this->redirectToRoute('app_login_supabase');

            } catch (RequestException $e) {
                $errorBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
                $this->addFlash('error', 'L’inscription a échoué. Veuillez réessayer.');
                $error = $errorBody; // utile en dev pour afficher l’erreur exacte
            }
        }

        return $this->render('pages/auth/register.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/magic-login/callback', name: 'app_magic_login_callback')]
    public function magicLoginCallback(Request $request, SessionInterface $session): Response
    {
        $accessToken  = $request->query->get('access_token');
        $refreshToken = $request->query->get('refresh_token');

        if (!$accessToken) {
            $this->addFlash('error', 'Lien magique invalide ou expiré.');
            return $this->redirectToRoute('app_login_supabase');
        }

        try {
            // Récupération du profil utilisateur à partir de l’access token
            $response = $this->client->get('/auth/v1/user', [
                'headers' => [
                    'apikey'        => $this->supabaseKey,
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $userData = json_decode((string) $response->getBody(), true);

            if (!is_array($userData) || !isset($userData['id'])) {
                $this->addFlash('error', 'Impossible de récupérer vos informations utilisateur.');
                return $this->redirectToRoute('app_login_supabase');
            }

            $session->set('access_token', $accessToken);
            $session->set('refresh_token', $refreshToken);
            $session->set('user', $userData);

            $this->addFlash('success', 'Connecté avec succès via magic link !');
            return $this->redirectToRoute('app_home');

        } catch (RequestException $e) {
            $this->addFlash('error', 'Erreur lors de la récupération du profil utilisateur.');
            return $this->redirectToRoute('app_login_supabase');
        }
    }

    #[Route('/logout', name: 'app_logout_supabase')]
    public function logout(SessionInterface $session): Response
    {
        $token = $session->get('access_token');

        if ($token) {
            try {
                $this->client->post('/auth/v1/logout', [
                    'headers' => [
                        'apikey'        => $this->supabaseKey,
                        'Authorization' => 'Bearer ' . $token,
                    ],
                ]);
            } catch (\Throwable $e) {
                // on ignore les erreurs de logout
            }
        }

        $session->clear();
        return $this->redirectToRoute('app_home');
    }
}
