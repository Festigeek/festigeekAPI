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
        Félicitations! Tu es inscrit à la LAN FestiGeek 2018 !<br/>
        Ou enfin presque, reste encore à régler la facture. ;)
      </p>

      <p>
        Pour commencer voici le récapitulatif de ta commande:<br/>
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
          <td style="text-align: right;"><strong>{{ number_format($order->total, 2) }} CHF</strong></td>
        </tr>
      </table>

      <p>
        Afin de valider ton inscription, nous te demandons d'effectuer un transfert de <strong>{{ $order->total }} CHF</strong> avant le <strong>07 mai</strong> à<br/>

        <span style="display: inline-block; border: 2px solid black; padding: 8px; margin-top: 8px;">
          PostFinance SA<br />
          Migerstrasse 20, 3030 Berne<br />
          FestiGeek<br />
          CH83 0900 0000 1448 4050 7
        </span>
      </p>

      <p>
          <strong>Note importante:</strong> indique la référence numéro <strong>{{ $order_id }}</strong> lors de ton virement pour qu'on puisse identifier ton paiement !
          <strong>Autre information importante:</strong> ne fait pas de paiement en <strong>bulletin de versement rouge</strong>, autrement ça te sera facturé 5 CHF de plus. Merci !
      </p>

      <p>
        Si tu as des questions, n'hésites-pas à nous contacter sur <a href="https://www.facebook.com/festigeek.yverdon/">Facebook</a> ou <a href="https://discord.gg/festigeek">Discord</a>.<br>
        On se réjouit de te voir à la LAN.
      </p>

      <p>
        A+<br/>
        L'équipe FestiGeek
      </p>

      <br />
      {{ HTML::image('images/logo.full.png', 'Logo Festigeek', array('style' => 'width:50%;')) }}

    </div>

  </body>
</html>
