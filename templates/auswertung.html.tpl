{include file=header.html.tpl title="Auswertung $title" class=auswertung}
{include file=surveyinfo.html.tpl title=$title desc=$desc sid=$id start=$start end=$end}
{if $tokens > 0 && $usetokens}<p class="wahlbeteiligung">Wahlbeteiligung: <span id="wahlbeteiligung">{math equation=v/t*100 v=$votes t=$tokens format="%.2f"} %</span> (<span id="votes">{$votes}</span> abgegebene Stimmen von <span id="tokens">{$tokens}</span> m&ouml;glichen Stimmen)</p>
{elseif !$usetokens}<p class="wahlbeteiligung">Es haben <span id="votes">{$votes}</span> Leute abgestimmt.</p>{/if}
<p class="links">
{if isset($loglink)}<a href="{$loglink}" class="loglink">Log anzeigen</a>{/if}
{if !isset($questions)}
<p class="info">Weitere Statistiken sind zum aktuellen Zeitpunkt nicht verf&uuml;gbar.{if $end != null} Das Ergebnis wird voraussichtlich am {$end|date_format:"%d.%m.%y"} verf&uuml;gbar sein.{/if}</p>
<p class="info">
 {if $start != null && $start > $smarty.now}Die Umfrage wird momentan vorbereitet. Sie startet am {$start|date_format:"%d.%m.%y"}.
 {elseif $end != null && $end < $smarty.now}Die Umfrage ist bereits seit dem {$end|date_format:"%d.%m.%y"} abgeschlossen - die Ergebnisse werden sicher bald eintreffen.
 {elseif $active}Die Umfrage ist noch aktiv. Der {mailto address=$ADMINMAIL text=$ADMINNAME} muss sie erst deaktivieren.
 {else $end != null && $end >= $smarty.now}Die Umfrage wurde bereits geschlossen. Das Ergebnis wird am {$end|date_format:"%d.%m.%y"} verf&uuml;gbar sein.{/if}
</p>
{else}
{if isset($tokenlist)}<a href="{$tokenlist}" class="tokenlist">Abstimmungsliste einsehen</a>{/if}
</p>
{foreach from=$questions item=question}
<h2 class="question">{$question.q|htmlescape}</h2>
<table>
<tr>
 <th colspan="4" class="heading">Auswertung</td>
 {if $usetokens}<th colspan="4" class="heading full">...mit Nichtw&auml;hlern</td>{/if}
</tr>
<tr>
 <td colspan="4" class="graph">{if isset($question.graph)}<img src="{$question.graph}" />{else}&nbsp;{/if}</td>
 {if $usetokens}<td colspan="4" class="graph full">{if isset($question.fullgraph)}<img src="{$question.fullgraph}" />{else}&nbsp;{/if}</td>{/if}
</tr>
<tr>
 <th>&nbsp;</th>
 <th>&nbsp;</th>
 <th>Stimmen</th>
 <th>Anteil</th>
 {if $usetokens}
 <th class="full">&nbsp;</th>
 <th class="full">&nbsp;</th>
 <th class="full">Stimmen</th>
 <th class="full">Anteil</th>
 {/if}
</tr><!-- P3N15 -->
{foreach from=$question.options item=option}
<tr>
 {if !isset($option.isnichtwaehler)}
 <th class="optimg"><img src="{$option.optimg}" /></th>
 <th class="answer">{$option.answer|htmlescape}</th>
 <td class="votes">{$option.votes}</td>
 <td class="anteil">{if $votes<=0}X{else}{math equation=v/t*100 v=$option.votes t=$votes format="%.2f"} %{/if}</td>
 {else}
 <th class="optimg">&nbsp;</th>
 <th class="answer">&nbsp;</th>
 <td class="votes">&nbsp;</td>
 <td class="anteil">&nbsp;</td>
 {/if}
 {if $usetokens}
 <th class="optimg full"><img src="{$option.optimg}" /></th>
 <th class="answer full">{$option.answer|htmlescape}</th>
 <td class="votes full">{$option.votes}</td>
 <td class="anteil full">{if $tokens<=0}X{else}{math equation=v/t*100 v=$option.votes t=$tokens format="%.2f"} %{/if}</td>
 {/if}
</tr>
{/foreach}
</table>
{/foreach}
{/if}
<span class="penis">PENIS</span>
{include file=footer.html.tpl}
