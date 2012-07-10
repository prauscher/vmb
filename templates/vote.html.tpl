{include file=header.html.tpl class=vote title="Umfrage $title" }
{include file=surveyinfo.html.tpl title=$title desc=$desc sid=$id start=$start end=$end}
{if isset($votecounted)}
<p class="done">Deine Stimme wurde erfolgreich gewertet! Danke f&uuml;r deine Meinung!</p>
<p class="info">Wenn du es dir noch einmal anders &uuml;berlegst, kannst du den Link aus der Mail erneut nutzen.</p>
<p><a href="{$auswertunglink|htmlescape}">Zur Auswertung</a></p>
<p><a href="{$votelink|htmlescape}">Zur&uuml;ck zum Abstimmen</a></p>
{elseif isset($error_couldnotcountvote)}
<p class="error">Deine Stimme konnte nicht gewertet werden - sprich bitte DRINGEND mit dem {mailto address=$ADMINMAIL text=$ADMINNAME}.</p>
{else}
{if isset($loadoldresults)}
<p class="info">Du hast schon einmal abgestimmt. Diese Felder sind nun vorausgew&auml;hlt und k&ouml;nnen von dir ver&auml;ndert werden.</p>
{/if}
<form action="vote.php" method="post">
 {include file="questions.html.tpl" questions=$questions}
 <fieldset>
  <input type="hidden" name="sid" value="{$id|htmlescape}" />
  <input type="hidden" name="lang" value="{$lang|htmlescape}" />
  <input type="hidden" name="token" value="{$token|htmlescape}" />
  <input type="submit" name="save" id="save" value="Speichern" />
  <input type="reset" id="reset" value="Zuruecksetzen" />
 </fieldset>
</form>
{/if}
{include file=footer.html.tpl}
