<?php

require_once("../config.inc.php");

$start = strftime($config->strftimeformat);
$end = strftime($config->strftimeformat, time()+7*24*60*60);

?>
<form action="createsurvey.php" accept-charset="<?= $config->charset ?>" method="post">
 <fieldset>
  <table>
  <tr>
   <th>Titel</th>
   <td><input type="text" name="title" size="40" /></td>
  </tr>
  <tr>
   <th>Start</th>
   <td><input type="text" name="start" value="<?= $start ?>" size="<?= strlen($start) ?>" /> (Feld r&auml;umen, falls nicht ben&ouml;tigt)</td>
  </tr>
  <tr>
   <th>Ende</th>
   <td><input type="text" name="end" value="<?= $end ?>" size="<?= strlen($end) ?>" /> (Feld r&auml;umen, falls nicht ben&ouml;tigt)</td>
  </tr>
  <tr>
   <th>Beschreibung</th>
   <td><textarea name="desc" rows="10" cols="70"></textarea></td>
  </tr>
  <tr>
   <th>Optionen (eine pro Zeile)</th>
   <td><textarea name="options" rows="7" cols="20"></textarea></td>
  </tr>
  </table>
  <input type="checkbox" name="multiselect" /> Wahl durch Zustimmung (Alternativ: Einfache Mehrheitswahl)<br />
  <input type="checkbox" name="usetokens" checked="checked" /> Verwende Tokens (Alternativ: Keine Sicherstellung)<br />
  <?php if (stripslashes($_SERVER["HTTP_HOST"]) == $config->adminhost) : ?>
  <input type="checkbox" name="private" checked="checked" /> Private Umfrage (Alternativ: Auf allen Hosts sichtbar)<br />
  <?php endif; ?>
  <input type="submit" name="create" value="Erstellen" />
 </fieldset>
</form>
