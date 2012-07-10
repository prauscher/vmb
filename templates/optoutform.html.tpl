{include file=header.html.tpl}
{if isset($alreadyoptouted)}
<p class="error">Diese Adresse steht bereits auf der Blackliste.</p>
{elseif isset($mailinvalid)}
<p class="error">Deine Mailadresse ist ung&uuml;ltig und stimmt nicht mit
 dem Hash in der Datenbank &uuml;berein.</p>
{elseif isset($couldnotoptout)}
<p class="error">Konnte deine Adresse nicht blacklisten. Bitte melde dich
 beim {mailto address=$ADMINMAIL text=$ADMINNAME}.</p>
{else}
<p class="info">Wenn du dich austragen m&ouml;chtest, trage bitte die
 Mailadresse, an die der Token geschickt wurde, unten ein.</p>
<form action="" method="post" accept-charset="{$CHARSET}">
 <fieldset>
  <label for="mail">Mailadresse:</label>
  <input type="text" id="mail" name="mail" />
  <input type="submit" id="submit" name="optout" value="Austragen" />
 </fieldset>
</form>
{/if}
{include file=footer.html.tpl}
