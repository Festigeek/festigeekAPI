<!DOCTYPE html>
<html>
  <head>
    <title>Entree LAN FestiGeek 2017</title>
    <meta charset="utf-8" />
    <style type="text/css">
      /*@font-face {
        font-family: 'manteka';
        src: url('fonts/manteka.eot');
        src: local('☺'), url('fonts/manteka.woff') format('woff'), url('fonts/manteka.ttf') format('truetype'), url('fonts/manteka.svg') format('svg');
        font-weight: normal;
        font-style: normal;
      }*/

      body {
        font-family: arial;
        font-size: 16pt;
      }

      .contenu {
        width: 700px;
        height: 300px;
        margin: 100px auto;
      }

      table {
        width: 100%;
        height: 100%;
        border: 1px solid black;
        border-spacing: 0;
        border-collapse: collapse;
      }

      td {

        background-color: lightGray;
        padding: 0;
      }

      .header {
        background-color: white;
        height: 50px;
      }

      .header2 td {
        background-color: #3F2E79;
      }

      .header td {
        background-color: white;
        height: 75px;
      }

      .logo {
        width: 150px;
        height: 150px;
        background: white url({{ base_path('public/images/logo_carre.png') }}) no-repeat center;
        background-size: 140px;
      }

      .logo-text {
        background: white url({{ base_path('public/images/logo_fg_text.png') }}) no-repeat center;
      }

      .deco-1 {
        width: 75px;
        height: 75px;
        background: lightGray url({{ base_path('public/images/deco1.png') }}) no-repeat center;
      }

      .reglage {
        width: 255px;
        text-align: center;
      }

      .qrcode {
        width: 200px;
        text-align: center;
      }

      .qrcode img {
        height: 200px;
      }

      .data {
        height: 50px;
        padding-left: 20px;
      }

      .titre {
        font-size: 20pt;
        font-weight: bold;
      }

      .infos {
        text-align: center;

      }

      .mini {
        font-size: 12pt;
      }

      .purple {
        color: #3F2E79;
      }
    </style>
  </head>
  <body>

    <div id="ticket" class="contenu">
      <table>
        <tr class="header2">
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr class="header">
          <td rowspan="2" class="logo">
              <img src="../../public/images/logo_carre.png" />
          </td>
          <td colspan="4" class="logo-text">&nbsp;</td>
        </tr>
        <tr>
          <td class="deco-1">&nbsp;</td>
          <td class="reglage">Du 26 au 28 Mai 2017<br/><span class="mini">(Ouverture des portes à 16h00)</span></td>
          <td rowspan="4" colspan="2" class="qrcode">
            <img src="data:image/png;base64,{{ $user->QRCode }}" alt="QR Code FestiGeek" height="200" />
          </td>
        </tr>
        <tr>
          <td colspan="3" class="data titre">
            <span class="purple">E</span>ntrée <span class="purple">P</span>ersonnelle <span class="purple">L</span>AN <span class="purple">2</span>017
          </td>
        </tr>
        <tr>
          <td colspan="3" class="data">
            {{ $user->username }} <span class="mini">({{ $user->email }})</span>
          </td>
        </tr>
        <tr>
          <td colspan="3" class="data infos mini">
            Commande No. 20{{ $order->id }}13
          </td>
        </tr>
      </table>
    </div>

    <div id="infos" class="contenu">
      Code d'acces au reseau de la LAN : {{ $order->code_lan }}
    </div>
  </body>
</html>
