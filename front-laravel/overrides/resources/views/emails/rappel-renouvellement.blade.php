<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h1 style="color: #198754; font-size: 20px;">NO MORE WASTE</h1>

        <p>Bonjour {{ $commercant['nom'] }},</p>

        @if ($joursRestants < 0)
            <p>
                Votre adhésion à l'association <strong>a expiré le
                {{ \Carbon\Carbon::parse($commercant['date_renouvellement'])->format('d/m/Y') }}</strong>
                ({{ abs($joursRestants) }} jour(s)).
            </p>
            <p>
                Pour continuer à bénéficier de nos services et de la collecte de vos invendus,
                merci de procéder au renouvellement de votre cotisation annuelle.
            </p>
        @else
            <p>
                Votre adhésion à l'association arrive à échéance le
                <strong>{{ \Carbon\Carbon::parse($commercant['date_renouvellement'])->format('d/m/Y') }}</strong>,
                soit dans {{ $joursRestants }} jour(s).
            </p>
            <p>
                Pensez à renouveler votre cotisation annuelle pour continuer à bénéficier de nos
                services et de la collecte de vos invendus.
            </p>
        @endif

        <p style="margin-top: 24px; padding-top: 12px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
            NO MORE WASTE — association de lutte contre le gaspillage<br>
            Ce message est envoyé automatiquement, merci de ne pas y répondre.
        </p>
    </div>
</body>
</html>
