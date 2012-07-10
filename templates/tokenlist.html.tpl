{include file=header.html.tpl class=tokenlist title="Tokenlist $title" }
<h1 class="title">{$title|htmlescape}</h1>
<p class="desc">{$desc|htmlescape|nl2br}</p>
<table>
<tr>
 <th>&nbsp;</th>
 <th>Zuletzt abgestimmt</th>
 {foreach from=$questions item=question}<th class="question">{$question.q|htmlescape}</th>{/foreach}
{foreach from=$tokens item=token}
<tr>
 <th class="token">{$token.token|htmlescape}</th>
 <th class="submitdate">{$token.submitdate|date_format:"%d.%m.%y %H:%M:%S"}</th>
 {foreach from=$questions item=question}{assign var=feldname value=$question.feldname}{assign var=q value=$token.$feldname}<td class="answer">
 {if is_array($q)}{foreach from=$q item=r}{if isset($question.options.$r.optimg)}<img src="{$question.options.$r.optimg}" />{/if} {$question.options.$r.answer|htmlescape} &nbsp; {/foreach}
 {else}{if isset($question.options.$q.optimg)}<img src="{$question.options.$q.optimg}" />{/if} {$question.options.$q.answer|htmlescape}{/if}</td>{/foreach}
</tr>
{/foreach}
</table>
{include file=footer.html.tpl}
