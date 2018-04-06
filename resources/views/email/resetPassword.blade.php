<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Réinitialisation du mot de passe de votre compte Festigeek.</h2>

        <div>
        	Bonjour {{ $username }} !<br />
        	Vous recevez ce mail suite à une demande de réinitialisation de mot de passe.<br />
            Au cas ou cette demande ne serait pas de votre fait, vous pouvez simplement ignorer ce mail.<br />
            <br />
        	Cliquez sur le lien ci-dessous pour modifier le mot de passe de votre compte :
            <br />
            <a href="{{ URL::to('https://www.festigeek.ch/#!/resetPassword/' . $reset_token . "/" . $email) }}">
                {{ URL::to('https://www.festigeek.ch/#!/resetPassword/' . $reset_token . "/" . $email) }}
            </a>
        </div>

        <br />
        <br />
        {{ Html::image('images/logo.full.png', 'Logo Festigeek', array('style' => 'width:50%;')) }}

    </body>
</html>