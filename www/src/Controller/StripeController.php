<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * Crée une session de paiement Stripe Checkout pour payer
     * 
     * @param Request $request le stripePriceId 
     * @return JsonResponse 
     */
    #[Route('/create-checkout-session', name: 'create-checkout-session', methods:['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['stripePriceId']) || !isset($data['email'])){
            return new JsonResponse([
                'error' => 'stripePriceId ou email manquant'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $stripePriceId = $data['stripePriceId'];
        $email = $data['email'];

        Stripe::setApiKey($_ENV['STRIPE_SK']);

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $email,
                'line_items' => [[
                    'price' => $stripePriceId,
                    'quantity' => 1,
                 ]],
                'mode' => 'subscription',
                'metadata' => [
                    'stripePricedId' => $stripePriceId,
                ],
                'success_url' => $_ENV['FRONTEND_URL']. '/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $_ENV['FRONTEND_URL']. '/cancel',
            ]);
            return new JsonResponse([
                'checkoutUrl' => $session->url
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Méthode qui récupère l'abonnement de stripe grace à l'id de la session
     * 
     * @param Request $request l'id de la session
     * @return JsonResponse l'abonnement
     */
    #[Route('/checkout-session/{sessionId}', name: 'checkout-session', methods: ['GET'])]
    public function getCheckoutSession(string $sessionId): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SK']);
        // On vérifie qu'on recoit bien sessionId
        if (!isset($sessionId) || empty($sessionId)) {
            return new JsonResponse(['error' => 'sessionId manquant'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $session = Session::retrieve($sessionId);
            if(!$session){
                return new JsonResponse(['error' => 'Session non trouvée'], JsonResponse::HTTP_NOT_FOUND);
            }

            $subscription = Subscription::retrieve($session->subscription);

            return new JsonResponse([
                'subscription' => $subscription
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}