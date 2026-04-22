<!DOCTYPE html>
<html>

<head>
    <title>Commercial - Confirmation d'inscription</title>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <img src="{{ $logoUrl }}" alt="Logo Maelys-Imo" width="150">
            </td>
        </tr>
        <tr>
            <td>
                <h1>Bienvenue parmi nos commerciaux</h1>
                <p>Votre compte commercial a été créé avec succès par l'administration.</p>
                @if (!empty($codeId))
                    <p>Votre identifiant de connexion (Code ID) est : <strong>{{ $codeId }}</strong></p>
                @endif
                <p>Pour activer votre accès et définir votre mot de passe, veuillez valider votre compte.</p>
                <p>Votre code de validation est : <strong>{{ $code }}</strong></p>
                <p><a href="{{ url('/validate-commercial-account/' . $email) }}"
                        style="background-color:#02245b; border: none; color: white; padding: 15px 32px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; cursor: pointer;">Valider
                        mon compte</a></p>
                <p>Merci de rejoindre l'aventure Maelys-Imo.</p>
            </td>
        </tr>
    </table>
</body>

</html>
