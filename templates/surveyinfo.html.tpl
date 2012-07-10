<h1 class="title">{$title|htmlescape}</h1>
<p id="info">Meinungsbild #<span class="sid">{$sid}</span>{if isset($start) && $start != null} vom <span class="start">{$start|date_format:"%d.%m.%y"}</span>{/if}{if isset($end) && $end != null} bis zum <span class="expire">{$end|date_format:"%d.%m.%y"}</span>{/if}.</p>
<p class="desc">{$desc|htmlescape|parselinks|nl2br}</p>
