<!DOCTYPE html>
<html>
<head>
    <title>Comptable - Confirmation d'inscription</title>
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
                <h1>Vous avez été bien enregistrer auprès de votre agence</h1>
                <p>Votre compte a été créé avec succès sur la plateforme.</p>
                <p>Cliquez sur le bouton ci-dessous pour valider votre compte.</p>
                <p>Saisissez le code <strong>{{ $code }}</strong> dans le formulaire qui apparaîtra.</p>
                <p><a href="{{ url('/validate-comptable-account/' . $email) }}" style="background-color:#02245b; border: none; color: white; padding: 15px 32px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; cursor: pointer;">Valider mon compte</a></p>
                <p>Merci d'utiliser notre palte-forme.</p>
            </td>
        </tr>
    </table>
</body>
</html>