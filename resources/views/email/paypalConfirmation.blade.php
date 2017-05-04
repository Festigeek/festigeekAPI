<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <style>
    table {
      width:50%;
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
    Félécitation tu es inscrit à la LAN FestiGeek 2017 !<br/>
    On a bien reçu ton payment PayPal MERCI !
  </p>

  <p>
    Voici le récapitulatif de ta commande<br/>
  </p>

  <table id="t01">
    <tr>
      <th>Nom</th>
      <th>Quantité</th>
      <th>Prix unitaire</th>
      <th>Total</th>
    </tr>

    @foreach ($order->products()->get() as $product)
    <tr>
      <td>{{$product->name}}</td>
      <td>{{$product->pivot->amount}}x</td>
      <td>{{$product->price}}.-</td>
      <td>{{$product->pivot->amount * $product->price}}.-</td>
    </tr>
    @endforeach
    <tr>
      <td>Total</td>
      <td></td>
      <td></td>
      <td><strong>{{$total}} CHF</strong></td>
    </tr>
  </table>

  <p>
    Si tu as des qusetions, n'hésites pas à nous contacter sur <a href="https://www.facebook.com/festigeek.yverdon/">Facebook</a> ou <a href="https://discord.gg/QQ2KEUY">Discord</a>.<br>
    On se réjouit de te voir à la LAN.
  </p>

  <p>
    A+<br/>
    L'équipe FestiGeek
  </p>

</div>

</body>
</html>

