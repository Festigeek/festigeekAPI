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
  </head>
  <body>
    <h2>Télécharge ton billet pour la LAN</h2>

    <div>
      <p>
        Salut {{ $username }}, <br/><br/>
      Nous espérons que tu es prêt pour notre LAN! Tu trouveras dans ce mail les informations
      importantes ainsi que ton billet electronique à télécharger. N'oublie pas de <strong>l'imprimer ou le mettre sur ton smartphone</strong>, le code sera scanné à l'entrée.<br/>

      </p>
      <button type="button" class="btn btn-primary"><a href="https://festigeek.ch/#!/profile">Télécharge ton billet ici</a></button>
      <p>
      Voici le récapitulatif de ta commande:<br/>
      </p>

      <table id="t01">
        <tr>
          <th style="text-align: center;">Nom</th>
          <th style="text-align: center;">Quantité</th>
          <th style="text-align: center;">Prix</th>
          <th style="text-align: center;">Total</th>
        </tr>

        @foreach ($order->products()->get() as $product)
        <tr>
          <td>{{ $product->name }}</td>
          <td style="text-align: center;">{{ $product->pivot->amount }}</td>
          <td style="text-align: right;">{{ number_format($product->price, 2) }}</td>
          <td style="text-align: right;">{{ number_format($product->pivot->amount * $product->price, 2) }}</td>
        </tr>
        @endforeach
        <tr>
          <td colspan="3">Total</td>
          <td style="text-align: right;"><strong>{{ number_format($total, 2) }} CHF</strong></td>
        </tr>
      </table>

      <p>
        Les portes ouvrent le <strong>vendredi 26 mai, à 16h00</strong>. Tu trouveras toutes les informations importantes <a href="https://festigeek.ch/#!/infolan">ici</a>.
      </p>

      <p>
        Si tu as des questions, n'hésites-pas à nous contacter sur <a href="https://www.facebook.com/festigeek.yverdon/">Facebook</a> ou <a href="https://discord.gg/QQ2KEUY">Discord</a>.<br>
        On se réjouit de te voir à la LAN.
      </p>

      <p>
        A bientôt!<br/>
        L'équipe FestiGeek
      </p>

    </div>

  </body>
</html>
