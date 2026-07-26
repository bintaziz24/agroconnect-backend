<?php

namespace App\Services\Payment\Gateways;

use App\Models\Commande;
use App\Services\Payment\PaymentGatewayInterface;

class CashGateway implements PaymentGatewayInterface
{
    public function process(Commande $commande, array $details = []): array
    {
        // Pour le paiement en espèces à la livraison, le paiement n'est pas immédiat
        // Il est marqué comme en attente (pending) et n'a pas encore d'ID de transaction

        return [
            'success'        => true,
            'transaction_id' => null,
            'status'         => 'pending',
            'message'        => 'Commande enregistrée en espèces à la livraison. Le paiement se fera à la réception auprès du livreur.',
            'metadata'       => [
                'provider' => 'cash',
                'api_mock' => true,
                'timestamp' => now()->toIso8601String()
            ]
        ];
    }
}
