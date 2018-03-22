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
  <h2>Confirmation de ton inscription</h2>

  <div>
    <p>
      Salut {{ $username }}, <br/><br/>
      Félicitation tu es inscrits à la LAN FestiGeek 2018 !<br/>
      On a bien reçu ton paiement PayPal, MERCI !
    </p>

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
      Si tu as des qusetions, n'hésites pas à nous contacter sur <a href="https://www.facebook.com/festigeek.yverdon/">Facebook</a> ou <a href="https://discord.gg/festigeek">Discord</a>.<br>
      On se réjouit de te voir à la LAN.
    </p>

    <p>
      A+<br/>
      L'équipe FestiGeek
    </p>

  </div>
</body>
</html>

