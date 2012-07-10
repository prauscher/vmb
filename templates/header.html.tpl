<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<title>PP Hessen {$title|default:"Meinungsbildungstool"}</title>
{capture assign=design}{$smarty.request.design|default:design2}{/capture}
<link rel="stylesheet" href="{getlink file="style.css"}" type="text/css" />
<link rel="stylesheet" href="{getlink file="$design.css"}" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; Charset={$CHARSET}" />
</head>
<body class="{$class}">
<img src="{getlink file=logo.png}" id="logo" />
<div id="content">
