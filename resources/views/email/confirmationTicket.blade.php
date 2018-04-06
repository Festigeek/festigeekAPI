<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <style>
      table {
        width: 650px;
      }
      table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
      }
      th, td {
        padding: 5px;
        text-align: left;
      }
      table#t01 tr:nth-child(even) {
        background-color: #eee;
      }
      table#t01 tr:nth-child(odd) {
        background-color:#fff;
      }
      table#t01 th {
        background-color: black;
        color: white;
      }
    </style>
    <title>Ton billet pour la LAN<</title>
  </head>

  <body>
    <h2>Ton billet pour la LAN</h2>

    <p>
      Salut {{ $user->username }}, <br/><br/>
      Nous espérons que tu es prêt pour notre LAN ! Tu trouveras dans ce mail les informations importantes ainsi que ton billet electronique.<br  />
      N'oublie pas de <strong>l'imprimer ou le mettre sur ton smartphone</strong>, le code sera scanné à l'entrée.
    </p>

    <p>Des douches seront mises à ta disposition. Si tu es intéressé, prends un linge !</p>

    @if ($order->state == 0)
      <div style="background-color:#ffaaaa;width:550px;margin:20px auto;border-radius:5px;text-align:center;">
          Nous n'avons <strong>toujours pas reçu ton paiement</strong>. Dans le cas où le paiement serait parti trop tard, nous te remercions d'avance de nous faire part d'une preuve de paiement. Sinon, il est encore possible de payer sur place, à titre exceptionel.
      </div>
    @endif

    @if (\Carbon\Carbon::createFromFormat('Y-m-d', $user->birthdate)->diffInYears(\Carbon\Carbon::now()) < 18)
      <div style="background-color:#ffaaaa;width:550px;margin:20px auto;border-radius:5px;text-align:center;">
        Minute ! On dirait bien que tu es <strong>mineur</strong> !<br />
        N'oublies pas de venir avec le formulaire d'autorisation pour mineurs !<br />
        {{ URL::to('https://www.festigeek.ch/assets/FG2017_consentement_parental.pdf') }}
      </div>
    @endif

    <h3>Voici le récapitulatif de ta commande:</h3>
    <p>
      <ul>
        @foreach ($order->products()->get() as $product)
          <li>{{ $product->pivot->amount }}x {{ $product->name }}</li>
        @endforeach
      </ul>
    </p>

    <p>
      Tu peux également le récupérer sur ta page de profil, sur le site:
    </p>

    <div style="text-align:center;">
      <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://www.festigeek.ch/#!/profile" style="height:40px;v-text-anchor:middle;width:200px;" arcsize="25%" strokecolor="#1e3650" fillcolor="#3f2e79">
        <w:anchorlock/>
        <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">Mon compte Festigeek</center>
      </v:roundrect>
      <![endif]-->
      <a href="https://www.festigeek.ch/#!/profile" style="background-color:#3f2e79;border:1px solid #1e3650;border-radius:10px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;mso-hide:all;">Mon compte Festigeek</a>
    </div>

    <p>
      Les portes ouvrent le <strong>vendredi 11 mai, à 16h00</strong>. Tu trouveras toutes les informations importantes <a href="https://festigeek.ch/#!/infolan">ici</a>.
    </p>

    <p>
      Si tu as des questions, n'hésites-pas à nous contacter sur <a href="https://www.facebook.com/festigeek.yverdon/">Facebook</a> ou <a href="https://discord.gg/festigeek">Discord</a>.<br>
      On se réjouit de te voir à la LAN.
    </p>

    <p>
      A bientôt!<br/>
      L'équipe FestiGeek
    </p>

    <br />
    {{ Html::image('images/logo.full.png', 'Logo Festigeek', array('style' => 'width:50%;')) }}

  </body>
</html>
