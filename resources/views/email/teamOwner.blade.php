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
    <h2>Tu es l'heureux créateur de l'équipe "{{ $team->name }}" !</h2>

    <div>
      <p>
        Salut {{ $username }}, <br/><br/>
        Félicitation! Tu es inscrit à la LAN FestiGeek 2018 !<br/>
        Mais si tu reçois ce mail, c'est que tu n'as pas prévu de venir seul, et ça c'est bien !
      </p>

      <p>
        Afin d'inviter tout tes amis (ou futur ex-amis, selon le jeu choisi), donne-leur à tous le code suivant:
      </p>

      <h3 style="text-align:center;">{{ $team->code }}</h3>


      <p>Il pourront ainsi effectuer leur commande en étant automatiquement ajouté à ton équipe. Si c'est pas beau la technologie...</p>

      <p>
        Comme toujours, si tu as des questions, n'hésites-pas à nous contacter sur <a href="https://www.facebook.com/festigeek.yverdon/">Facebook</a> ou <a href="https://discord.gg/festigeek">Discord</a>.<br>
        On se réjouit de tous vous voir à la LAN.
      </p>

      <p>
        A+<br/>
        L'équipe FestiGeek
      </p>

    </div>

  </body>
</html>
