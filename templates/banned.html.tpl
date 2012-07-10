{include file=header.html.tpl class=banned}
<h1>Gesperrt</h1>
<p class="error">
 Leider m&uuml;ssen wir davon ausgehen, dass du / jemand von deiner IP
 &quot;{$ip}&quot; versucht, das System zu kompromitieren.
</p>
<p>
 Aus diesem Grund werden diese Zugriffe bis zum {$banTill|date_format:"%d.%m.%Y"}
 geblockt.
</p>
<p>
 Bitte melde dich bei {mailto address=$ADMINMAIL text=$ADMINNAME}, wenn du das
 f&uuml;r nicht gerechtfertigt h&auml;lst.
</p>
{include file=footer.html.tpl}
