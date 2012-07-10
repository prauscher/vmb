{include file=header.html.tpl title="Umfrage $title"}
<p class="error">
 {if $start != null && $start > $smarty.now}Die Umfrage wird momentan vorbereitet. Sie startet am {$start|date_format:"%d.%m.%y"}.
 {elseif $end != null && $end < $smarty.now}Die Umfrage ist bereits abgeschlossen. Sie wurde am {$end|date_format:"%d.%m.%y"} beendet. Die <a href="auswertung.php?id={$id}">Auswertung</a> folgt in K&uuml;rze.
 {elseif ! $active}Die Umfrage ist momentan inaktiv.{/if}
</p>
{include file=footer.html.tpl}
