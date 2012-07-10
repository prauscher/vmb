{foreach from=$questions item=question}
 <h2 class="questiontitle">{$question.title|htmlescape}</h2>
 <fieldset class="options">
  {if isset($question.multiselect)}
   {html_checkboxes name=$question.name options=$question.options selected=$question.selectedoptions separator="<br />"}
  {else}
   {html_radios name=$question.name options=$question.options selected=$question.value separator="<br />"}
  {/if}
  </fieldset>
 {/foreach}
