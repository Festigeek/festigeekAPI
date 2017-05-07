<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <style>
      table {
        width:100%;
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
        Félicitation! Tu es inscrit à la LAN FestiGeek 2017 !<br/>
        Ou enfin presque, reste encore à régler la facture. ;)
      </p>

      <p>
        Pour commencer voici le récapitulatif de ta commande:<br/>
      </p>

      <table id="t01">
        <tr>
          <th>Nom</th>
          <th>Quantité</th>
          <th>Prix</th>
          <th>Total</th>
        </tr>

        @foreach ($order->products()->get() as $product)
          <tr>
            <td>{{ $product->name}}</td>
            <td style="text-align: center;">{{ $product->pivot->amount }}</td>
            <td style="text-align:right;">{{ number_format($product->price, 2) }}.-</td>
            <td style="text-align:right;">{{ number_format($product->pivot->amount * $product->price, 2) }}.-</td>
          </tr>
        @endforeach
        <tr>
          <td>Total</td>
          <td></td>
          <td></td>
          <td style="text-align:right;"><strong>{{ number_format($total, 2) }} CHF</strong></td>
        </tr>
      </table>

      <p>
        Afin de valider ton inscription, nous te demandons d'effectuer un transfert de <strong>{{ $total }} CHF</strong> avant le <strong>22 mai</strong> à<br/>

        <div style="width:100%; border:2px solid black;padding:5px;">
          PostFinance SA<br />
          Migerstrasse 20, 3030 Berne<br />
          FestiGeek<br />
          CH83 09000 0001 4484 0507
        </div>
        <p><strong>Note importante:</strong> indique la référence numéro <strong>20{{ $order_id }}13</strong> lors de ton virement pour qu'on puisse identifier ton paiement!</p>
      </p>

      <p>
        Si tu as des questions, n'hésites-pas à nous contacter sur <a href="https://www.facebook.com/festigeek.yverdon/">Facebook</a> ou <a href="https://discord.gg/QQ2KEUY">Discord</a>.<br>
        On se réjouit de te voir à la LAN.
      </p>

      <p>
        A+<br/>
        L'équipe FestiGeek
      </p>

    </div>

  </body>
</html>
