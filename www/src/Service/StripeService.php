<?php

namespace App\Service;

use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripeService
{
    public function __construct(private string $stripeSecretKey)
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Méthode qui permet de créer un plan d'abonnement
     * 
     * @param string $name Nom du plan
     * @param int $price Prix en centimes
     * @param string $interval "month" ou "year"
     * @return array Retourne les IDs du produit et du prix Stripe
     */
    public function createSubscriptionPlan(string $name, int $price, string $interval)
    {
        // Création du produit Stripe
        $product = Product::create([
            'name' => $name,
            'type' => 'service'
        ]);

        // Création du prix Stripe
        $priceData = Price::create([
            'unit_amount' => $price, // Prix en centimes
            'currency' => 'eur',
            'recurring' => ['interval' => $interval], // "month" ou "year"
            'product' => $product->id
        ]);

        // on retourne notre tableau d'IDs
        return [
            'productId' => $product->id,
            'priceId' => $priceData->id
        ];
    }
}