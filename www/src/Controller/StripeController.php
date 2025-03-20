<?php

namespace App\Controller;

use Stripe\Price;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
  /**
   * crée une session de paiement Stripe Checkout pour payer
   * 
   * @param Request $request le stripePriceId et l'email
   * @return JsonResponse 
   */
  #[Route('/create-checkout-session', name: 'create-checkout-session', methods: ['POST'])]
  public function createCheckoutSession(Request $request): JsonResponse
  {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['stripePriceId']) || !isset($data['email'])) {
      return new JsonResponse(['error' => 'stripePriceId ou email manquant'], JsonResponse::HTTP_BAD_REQUEST);
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
          'stripePriceId' => $stripePriceId // ✅ Ajout des métadonnées pour le webhook
        ],
        'success_url' => $_ENV['FRONTEND_URL'] . '/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $_ENV['FRONTEND_URL'] . '/cancel',
      ]);

      return new JsonResponse(['checkoutUrl' => $session->url]);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * méthode qui récupère l'abonnement de stripe grace à l'id de la session
   * 
   * @param string $sessionId l'id de la session
   * @return JsonResponse l'abonnement
   */
  #[Route('/checkout-session/{sessionId}', name: 'checkout-session', methods: ['GET'])]
  public function getCheckoutSession(string $sessionId): JsonResponse
  {
    Stripe::setApiKey($_ENV['STRIPE_SK']);
    //on verifie qu'on recoit bien sessionId
    if (!isset($sessionId) || empty($sessionId)) {
      return new JsonResponse(['error' => 'sessionId manquant'], JsonResponse::HTTP_BAD_REQUEST);
    }

    try {
      $session = Session::retrieve($sessionId);

      if (!$session) {
        return new JsonResponse(['error' => 'Session non trouvée'], JsonResponse::HTTP_NOT_FOUND);
      }

      $subscription = Subscription::retrieve($session->subscription);

      return new JsonResponse([
        'subscription' => $subscription
      ]);
    } catch (\Exception $e) {
      return new JsonResponse(
        ['error' => $e->getMessage()],
        JsonResponse::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * méthode qui récupère l'abonnement de stripe grace à l'id de l'utilisateur
   * 
   * @param Request $request l'id de l'abonnement
   * @return JsonResponse l'abonnement
   */
  #[Route('/user/subscription', name: 'user_subscription', methods: ['POST'])]
  public function getUserSubscription(Request $request): JsonResponse
  {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['subscriptionId']) || empty($data['subscriptionId'])) {
      return new JsonResponse(['error' => 'subscriptionId manquant'], JsonResponse::HTTP_BAD_REQUEST);
    }

    $subscriptionId = $data['subscriptionId'];

    Stripe::setApiKey($_ENV['STRIPE_SK']);

    try {
      $subscription = Subscription::retrieve($subscriptionId);
      //on recupère le produit lié à l'abonnement
      $product = Product::retrieve($subscription->items->data[0]->price->product);
      return new JsonResponse([
        'subscription' => $subscription,
        'product' => $product
      ]);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * méthode qui résilie un abonnement
   * 
   * @param Request $request l'id de l'abonnement
   * @return JsonResponse le message de résiliation
   */
  #[Route('/user/cancel-subscription', name: 'cancel-subscription', methods: ['POST'])]
  public function cancelSubscription(Request $request): JsonResponse
  {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['subscriptionId']) || empty($data['subscriptionId'])) {
      return new JsonResponse(['error' => 'subscriptionId manquant'], JsonResponse::HTTP_BAD_REQUEST);
    }

    $subscriptionId = $data['subscriptionId'];

    Stripe::setApiKey($_ENV['STRIPE_SK']);

    try {
      $cancel = new Subscription($subscriptionId);
      $cancel->cancel();

      return new JsonResponse(['message' => 'Abonnement résilié avec succès']);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * méthode qui réactive un abonnement
   * 
   * @param Request $request l'id de l'abonnement
   * @return JsonResponse le message de réactivation
   */
  #[Route('/user/reactivate-subscription', name: 'reactivate-subscription', methods: ['POST'])]
  public function reactivateSubscription(Request $request): JsonResponse
  {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['subscriptionId']) || empty($data['subscriptionId'])) {
      return new JsonResponse(['error' => 'subscriptionId manquant'], JsonResponse::HTTP_BAD_REQUEST);
    }

    $subscriptionId = $data['subscriptionId'];

    Stripe::setApiKey($_ENV['STRIPE_SK']);

    try {
      Subscription::update($subscriptionId, [
        'cancel_at_period_end' => false
      ]);

      return new JsonResponse(['message' => 'Abonnement réactivé avec succès']);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * méthode qui met en pause un abonnement
   * 
   * @param Request $request l'id de l'abonnement
   * @return JsonResponse le message de mise en pause
   */
  #[Route('/user/pause-subscription', name: 'pause-subscription', methods: ['POST'])]
  public function pauseSubscription(Request $request): JsonResponse
  {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['subscriptionId']) || empty($data['subscriptionId'])) {
      return new JsonResponse(['error' => 'subscriptionId manquant'], JsonResponse::HTTP_BAD_REQUEST);
    }

    $subscriptionId = $data['subscriptionId'];

    Stripe::setApiKey($_ENV['STRIPE_SK']);

    try {
      Subscription::update($subscriptionId, [
        'cancel_at_period_end' => true
      ]);

      return new JsonResponse(['message' => 'Abonnement mis en pause avec succès']);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}