<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Vérification de l'adresse e-mail de votre compte Festigeek.</h2>

        <div>
        	Bonjour {{ $username }} !<br />
        	Merci d'avoir crée un compte sur le site de Festigeek !<br />
        	Cliquez sur le lien ci-dessous pour vérifier l'adresse e-mail de votre compte.
         <br />
         <br />
            {{ URL::to('http://localhost:9000/#/activate/' . $registration_token) }}.<br/>
        </div>

    </body>
</html>