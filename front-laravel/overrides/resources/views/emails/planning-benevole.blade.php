<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h1 style="color: #198754; font-size: 20px;">NO MORE WASTE</h1>

        <p>Bonjour {{ $benevole['nom'] }},</p>

        <p>
            Voici votre planning pour les {{ $jours }} prochains jours, en pièce jointe au
            format Excel.
        </p>

        @if ($affectations === [])
            <p>Aucune mission ne vous est affectée sur cette période.</p>
        @else
            <p>Vos {{ count($affectations) }} mission(s) :</p>
            <ul>
                @foreach ($affectations as $a)
                    <li>
                        <strong>{{ \Carbon\Carbon::parse($a['date'])->format('d/m/Y à H:i') }}</strong> —
                        {{ $a['type'] }} : {{ $a['libelle'] }}
                        @if ($a['lieu']) ({{ $a['lieu'] }}) @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <p>Merci pour votre engagement à nos côtés.</p>

        <p style="margin-top: 24px; padding-top: 12px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
            NO MORE WASTE — association de lutte contre le gaspillage<br>
            Ce message est envoyé automatiquement, merci de ne pas y répondre.
        </p>
    </div>
</body>
</html>
