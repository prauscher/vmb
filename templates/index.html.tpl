{include file=header.html.tpl class=index}
<h1>PPH-Umfrage</h1>
<p>Dieses Tool dient der Erfassung von Meinungen innerhalb der Piratenpartei
 Hessen. Den genauen Vorgang findet ihr im
 <a href="http://wiki.piratenpartei.de/HE:Meinungsbild-Tool" target="_blank">Wiki</a>.
</p>
<p>Um mit abstimmen zu k&ouml;nnen, musst du Mitglied bei der Piratenpartei
 Hessen sein. Bei jedem Meinungsbild wird dir daf&uuml;r eine Mail mit einem
 eindeutigen Link an deine Adresse geschickt. Mit diesem Link kannst du
 dann abstimmen.</p>
<table>
{assign var=time value=$smarty.now}
{foreach from=$surveys item=survey}
{if ($survey.end<$time)}{assign var=expired value=1}{/if}
{if ($survey.start>$time)}{assign var=waiting value=1}{/if}
<tr>
 <td class="status">
  {if !$survey.active && ($expired || $survey.end == null)}<img src="{getlink file="img/status_closed.png"}" alt="Beendet" title="Diese Umfrage ist beendet. Die Auswertung steht zur Verf&uuml;gung." />
  {elseif $survey.active && (!$waiting || $survey.start == null) && (!$expired || $survey.end == null)}<img src="{getlink file="img/status_active.png"}" alt="Aktiv" title="Diese Umfrage l&auml;uft momentan. Bitte stimme mit dem Link aus der Mail ab." />
  {else}<img src="{getlink file="img/status_expired.png"}" alt="Ausgelaufen" title="{if (!$survey.active && !$waiting && !$expired)}Die Umfrage wurde noch nicht aktiviert.{elseif (!$survey.active && $waiting && !$expired)}Diese Umfrage wird momentan vorbereitet.{elseif ($survey.active && $waiting)}Diese Umfrage wird am {$survey.start|date_format:"%d.%m.%y"} starten.{elseif ($survey.active && $expired)}Diese Umfrage ist ausgelaufen. Das Ergebnis wird bald bereitstehen.{/if}" />{/if}
 </td>
 <th class="title">{if !$survey.usetokens && $survey.active}<a href="vote.php?sid={$survey.id}&amp;lang={$survey.lang}">{/if}{$survey.title|htmlescape}{if !$survey.usetokens}</a>{/if}</th>
 <th class="start">{if isset($survey.start) && $survey.start != null}{$survey.start|date_format:"%d.%m.%y"}{/if}</th>
 <th class="expires">{if isset($survey.end) && $survey.end != null}{$survey.end|date_format:"%d.%m.%y"}{/if}</th>
 <td class="auswertunglink"><a href="auswertung.php?id={$survey.id|escape:url|htmlescape}&amp;lang={$survey.lang|escape:url|htmlescape}">Auswertung</a></td>
</tr>
{/foreach}
</table>
{include file=footer.html.tpl}
