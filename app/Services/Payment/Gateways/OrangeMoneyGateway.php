<?php

namespace App\Services\Payment\Gateways;

use App\Models\Commande;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Str;

class OrangeMoneyGateway implements PaymentGatewayInterface
{
    public function process(Commande $commande, array $details = []): array
    {
        // Simulation d'un appel API Orange Money (OM API)
        
        $telephone = $details['telephone'] ?? $commande->telephone;
        
        // Simuler le succès du paiement OM
        $transactionId = 'OM-' . strtoupper(Str::random(12));

        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'status'         => 'completed',
            'message'        => 'Paiement Orange Money traité avec succès pour le numéro : ' . $telephone,
            'metadata'       => [
                'provider' => 'orange_money',
                'phone'    => $telephone,
                'api_mock' => true,
                'timestamp' => now()->toIso8601String()
            ]
        ];
    }
}
