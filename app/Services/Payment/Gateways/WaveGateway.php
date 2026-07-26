<?php

namespace App\Services\Payment\Gateways;

use App\Models\Commande;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Str;

class WaveGateway implements PaymentGatewayInterface
{
    public function process(Commande $commande, array $details = []): array
    {
        // Simulation d'un appel API Wave (ex: création de session de paiement Wave)
        // Dans une vraie intégration, on ferait un appel HTTP curl vers l'API de Wave.
        
        $telephone = $details['telephone'] ?? $commande->telephone;
        
        // Simuler le succès du paiement en ligne
        $transactionId = 'WV-' . strtoupper(Str::random(12));

        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'status'         => 'completed',
            'message'        => 'Paiement Wave traité avec succès pour le numéro : ' . $telephone,
            'metadata'       => [
                'provider' => 'wave',
                'phone'    => $telephone,
                'api_mock' => true,
                'timestamp' => now()->toIso8601String()
            ]
        ];
    }
}
