<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirme seu e-mail no Assin.art</title>
</head>
<body style="margin: 0; padding: 24px; background-color: #f7f6f2; font-family: Arial, sans-serif; color: #121316;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border: 3px solid #000000; border-radius: 16px;">
        <tr>
            <td style="padding: 32px;">
                <h1 style="margin: 0 0 16px; font-size: 24px; line-height: 1.25; color: #000000;">
                    Confirme seu e-mail no Assin.art
                </h1>
                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.5;">
                    Olá, {{ $artistName }}! Falta pouco para confirmar o seu endereço de e-mail e proteger sua conta.
                </p>
                <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.5;">
                    Clique no botão abaixo para validar o seu e-mail:
                </p>
                <p style="margin: 0 0 24px;">
                    <a href="{{ $verificationUrl }}"
                       style="display: inline-block; padding: 14px 24px; background-color: #ffe600; color: #000000; font-weight: bold; text-decoration: none; border: 2px solid #000000; border-radius: 12px;">
                        Confirmar meu e-mail
                    </a>
                </p>
                <p style="margin: 0 0 8px; font-size: 14px; line-height: 1.5; color: #333333;">
                    Se o botão não funcionar, copie e cole o link abaixo no navegador:
                </p>
                <p style="margin: 0 0 24px; font-size: 13px; line-height: 1.4; word-break: break-all;">
                    <a href="{{ $verificationUrl }}" style="color: #000000;">{{ $verificationUrl }}</a>
                </p>
                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #555555;">
                    Este link expira em 24 horas e pode ser utilizado apenas uma vez. Se você não criou esta conta, ignore este e-mail.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
