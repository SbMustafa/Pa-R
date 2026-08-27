<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récapitulatif de livraison</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .entete { border-bottom: 2px solid #198754; padding-bottom: 10px; margin-bottom: 20px; }
        .entete h1 { color: #198754; font-size: 20px; margin: 0 0 4px 0; }
        .entete .sous-titre { color: #666; font-size: 11px; }
        .bloc { margin-bottom: 18px; }
        .bloc h2 { font-size: 13px; margin: 0 0 6px 0; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f1f1f1; text-align: left; padding: 6px; font-size: 11px; border-bottom: 1px solid #ccc; }
        td { padding: 6px; border-bottom: 1px solid #eee; }
        .total { font-weight: bold; }
        .signature { margin-top: 40px; }
        .signature-case { border: 1px solid #999; height: 70px; width: 45%; margin-top: 6px; }
        .pied { margin-top: 30px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="entete">
        <h1>NO MORE WASTE</h1>
        <div class="sous-titre">Récapitulatif de livraison n°{{ $tournee['id'] }}</div>
    </div>

    <div class="bloc">
        <h2>Livraison</h2>
        <table>
            <tr>
                <td style="width: 30%;"><strong>Destinataire</strong></td>
                <td>
                    {{ $tournee['destinataire'] }}
                    ({{ $tournee['type_destinataire'] === 'association' ? 'association caritative' : 'particulier' }})
                </td>
            </tr>
            <tr>
                <td><strong>Adresse</strong></td>
                <td>{{ $tournee['adresse'] ?: '—' }}</td>
            </tr>
            <tr>
                <td><strong>Date de la tournée</strong></td>
                <td>{{ \Carbon\Carbon::parse($tournee['date_tournee'])->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Bénévole en charge</strong></td>
                <td>{{ $benevole['nom'] ?? 'Non affecté' }}</td>
            </tr>
            <tr>
                <td><strong>Statut</strong></td>
                <td>{{ ['planifiee' => 'Planifiée', 'en_cours' => 'En cours', 'livree' => 'Livrée'][$tournee['statut']] ?? $tournee['statut'] }}</td>
            </tr>
        </table>
    </div>

    <div class="bloc">
        <h2>Produits livrés</h2>
        <table>
            <thead>
                <tr>
                    <th>Code-barre</th>
                    <th>Produit</th>
                    <th style="text-align: right;">Quantité</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lignes as $ligne)
                    <tr>
                        <td>{{ $ligne['code_barre'] }}</td>
                        <td>{{ $ligne['nom'] }}</td>
                        <td style="text-align: right;">{{ $ligne['quantite'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucun produit chargé.</td></tr>
                @endforelse
                <tr class="total">
                    <td colspan="2">Total</td>
                    <td style="text-align: right;">{{ collect($lignes)->sum('quantite') }} article(s)</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($tournee['notes'])
        <div class="bloc">
            <h2>Notes</h2>
            <p>{{ $tournee['notes'] }}</p>
        </div>
    @endif

    <div class="signature">
        <strong>Signature du destinataire</strong>
        <div class="signature-case"></div>
    </div>

    <div class="pied">
        Document généré le {{ now()->format('d/m/Y à H:i') }} — NO MORE WASTE, association de lutte contre le gaspillage
    </div>
</body>
</html>
