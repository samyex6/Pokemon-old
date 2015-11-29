<?php/*	[Discuz!] (C)2001-2007 Comsenz Inc.	This is NOT a freeware, use is subject to license terms	$RCSfile: template.func.php,v $	$Revision: 1.17.2.2 $	$Date: 2007/03/21 15:52:38 $*/if(!defined('TEMPLATE_DEBUGGING')) define('TEMPLATE_DEBUGGING', FALSE);function parse_template($file, $templateid, $tpldir) {	//ob_start();	$nest = 5;	$tplfile = ROOT . '/' . $tpldir . '/' . $file . '.php';	$objfile = CACHE_DIR . '/tpl_' . $templateid . '_' . $file . '.php';	if(!@$fp = fopen($tplfile, 'r')) {		exit('Current template file \'./$tpldir/$file.php\' not found or have no access!');	}	$template = fread($fp, filesize($tplfile));	fclose($fp);	$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";	$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";	$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);	$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}\n", $template);	$template = str_replace("{LF}", "<?=\"\\n\"?>\n", $template);	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);	$template = preg_replace_callback("/$var_regexp/s" ,'addquote', $template);	$template = preg_replace_callback("/\<\?\=\<\?\=$var_regexp\?\>\?\>/s", 'addquote', $template);	$template = "\n$template";	$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is", "\n<?php include template('\\1'); ?>\n", $template);	$template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is", "\n<?php include template(\\1); ?>\n", $template);	$template = preg_replace("/[\n\r\t]*\{css\s+(\S+)}[\n\r\t]*/is", "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo CC::css(\\1); ?>\">\n", $template);	$template = preg_replace("/[\n\r\t]*\{css\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo CC::css(\\1, \\2); ?>\">\n", $template);	$template = preg_replace_callback("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/is",        function($matches) {            return str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", "\n<?php $matches[1] ?>\n"));        }, $template);	$template = preg_replace_callback("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/is",	    function($matches) {            return str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", "\n<?php echo $matches[1]; ?>\n"));        }, $template);	$template = preg_replace_callback("/[\n\r\t]*\{elseif\s+(.+?)\}[\n\r\t]*/is",        function($matches) {            return str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", "\n<?php } elseif($matches[1]) { ?>\n"));        }, $template);	$template = preg_replace("/[\n\r\t]*\{else\}[\n\r\t]*/is", "\n<?php } else { ?>\n", $template);	for($i = 0; $i < $nest; $i++) {		$template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/is",            function($matches) {                return str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", "<?php if(is_array($matches[1])) { foreach($matches[1] as $matches[2]) { ?>")) .                        str_replace("\\\"", "\"", "$matches[3]<?php } } ?>");            }, $template);		$template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}*/is",            function($matches) {                return str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", "<?php if(is_array($matches[1])) { foreach($matches[1] as $matches[2] => $matches[3]) { ?>")) .                str_replace("\\\"", "\"", "$matches[4]<?php } } ?>");            }, $template);		$template = preg_replace_callback("/[\n\r\t]*\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}[\n\r\t]*/is",            function($matches) {                return str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", "<?php if($matches[1]) { ?>")) .                str_replace("\\\"", "\"", "$matches[2]<?php } ?>");            }, $template);	}	$template = preg_replace("/([^\{])\{$const_regexp\}([^\{])/s", "\\1<?=\\2?>\\3", $template);	if(TEMPLATE_DEBUGGING) $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);	if(!@$fp = fopen($objfile, 'w')) {		exit("$objfile Directory not found or have no access!");	}	$template = preg_replace_callback("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/", 'transamp', $template);	$template = preg_replace_callback("/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/is", "stripscriptamp", $template);	flock($fp, 2);	fwrite($fp, trim(sanitize($template)) . "\n");	fclose($fp);}function sanitize($buffer) {	if(TEMPLATE_DEBUGGING) return $buffer;    $search = [        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space        '/[^\S ]+\</s',  // strip whitespaces before tags, except space        '/(\s)+/s'       // shorten multiple whitespace sequences    ];    $replace = [ '>', '<', '\\1'];    $buffer  = preg_replace($search, $replace, $buffer);    return $buffer;}function transamp($matches) {    $str = $matches[0];	$str = str_replace('&', '&amp;', $str);	$str = str_replace('&amp;amp;', '&amp;', $str);	$str = str_replace('\"', '"', $str);	return $str;}function addquote($matches) {	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", "<?=$matches[1]?>"));}function stripvtags($expr, $statement) {	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));	$statement = str_replace("\\\"", "\"", $statement);	return $expr.$statement;}function stripscriptamp($matches) {	$s = str_replace('&amp;', '&', $matches[1]);	return "<script src=\"$s\" type=\"text/javascript\"></script>";}function template($file, $templateid = 0, $tpldir = '') {    $tplrefresh = 1;    $tpldir     = $tpldir ? $tpldir : 'source-tpl';    $tplfile    = ROOT . '/' . $tpldir . '/' . $file . '.php';    $objfile    = CACHE_DIR . '/tpl_' . $templateid . '_' . $file . '.php';    if($tplrefresh === 1 || ($tplrefresh > 1 && substr($GLOBALS['timestamp'], -1) > $tplrefresh)) {        $objfiletime = @filemtime($objfile);        if(@filemtime($tplfile) > ($objfiletime === FALSE ? 0 : $objfiletime)) {            require_once ROOT . '/include/function-template.php';            parse_template($file, $templateid, $tpldir);        }    }    return $objfile;}