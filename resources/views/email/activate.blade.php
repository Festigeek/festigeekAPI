<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Vérification de l'adresse e-mail de votre compte Festigeek.</h2>

        <div>
        	Salut {{ $username }} !<br />
        	Merci d'avoir créé un compte sur le site de Festigeek !<br />
        	Cliquez sur le lien ci-dessous pour vérifier l'adresse e-mail de votre compte.<br />
            <br />
            <a href="{{ URL::to('https://www.festigeek.ch/#!/activate/' . $registration_token) }}">
                {{ URL::to('https://www.festigeek.ch/#!/activate/' . $registration_token) }}
            </a>
        </div>

        <br />
        <br />
        {{ HTML::image('images/logo.full.png', 'Logo Festigeek', array('style' => 'width:50%;')) }}

    </body>
</html>
