<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: irid.skin.php,v 1.1.1 2005/11/07 14:02:10 miko Exp $
// Original is ari-
//
// Warning: eucjp version only.

// ���Ѥ��륹������
$irid_style_name = "cloudwalk";

// Prohibit direct access
if (! defined('UI_LANG')) die('UI_LANG is not set');
if (! isset($_LANG)) die('$_LANG is not set');
if (! defined('PKWK_READONLY')) die('PKWK_READONLY is not set');

$link  = & $_LINK;
$image = & $_IMAGE['skin'];
$rw    = ! PKWK_READONLY;

// Output HTTP headers
pkwk_common_headers();
header('Cache-control: no-cache');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=' . CONTENT_CHARSET);

// Output HTML DTD, <html>, and receive content-type
if (isset($pkwk_dtd)) {
	$meta_content_type = pkwk_output_dtd($pkwk_dtd);
} else {
	$meta_content_type = pkwk_output_dtd();
}

// Plus! not use $meta_content_type. because meta-content-type is most browser not used. umm...
?>
<head>
 <meta http-equiv="content-type" content="application/xhtml+xml; charset=<?php echo CONTENT_CHARSET ?>" />
 <meta http-equiv="content-style-type" content="text/css" />
<?php if (PKWK_ALLOW_JAVASCRIPT) { ?> <meta http-equiv="Content-Script-Type" content="text/javascript" /><?php } ?>
<?php if ($nofollow || ! $is_read)  { ?> <meta name="robots" content="NOINDEX,NOFOLLOW" /><?php } ?>

 <title><?php echo $title ?> - <?php echo $page_title ?></title>
 <link rel="stylesheet" href="skin/<?php echo $irid_style_name ?>/<?php echo $irid_style_name ?>.css" title="<?php echo $irid_style_name ?>" type="text/css" charset="Shift_JIS" />
 <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $link['rss'] ?>" />

<?php if (PKWK_ALLOW_JAVASCRIPT && $trackback_javascript) { ?> <script type="text/javascript" src="skin/trackback.js"></script><?php } ?>

<?php echo $head_tag ?>
</head>
<body>
<div id="wrapper"><!-- ��BEGIN id:wrapper -->
<!-- �� Header �� ========================================================== -->
<div id="header">
<div id="logo"><a href="<?php echo $link_top ?>"><?php echo $page_title ?></a></div>
</div>
<!-- �� Navigator �� ======================================================= -->
<?php
 if (exist_plugin('navibar2')) {
  echo do_plugin_convert('navibar2');
 } else {
  echo '<div id="navigator">';
  echo convert_html(get_source('SiteNavigator'));
  echo '</div>';
?>
</div>
<!-- �� Content �� ========================================================= -->
<div id="main"><!-- ��BEGIN id:main -->
<div id="wrap_content"><!-- ��BEGIN id:wrap_content -->
<div id="content"><!-- ��BEGIN id:content -->
<div id="page_navigator">
<?php echo convert_html(get_source('PageNavigator')); ?>
</div>
<h1 class="title"><?php echo $page ?></h1>
<?php if ($lastmodified != '') { ?><!-- ��BEGIN id:lastmodified -->
<div id="lastmodified">Last-modified: <?php echo $lastmodified ?></div>
<?php } ?><!-- ��END id:lastmodified -->
<div id="body"><!-- ��BEGIN id:body -->
<?php echo $body ?>
</div><!-- ��END id:body -->
<div id="summary"><!-- ��BEGIN id:summary -->
<?php if ($notes != '') { ?><!-- ��BEGIN id:note -->
<div id="note">
<?php echo $notes ?>
</div>
<?php } ?><!-- ��END id:note -->
<div id="trackback"><!-- ��BEGIN id:trackback -->
<?php
  if ($trackback) {
    $tb_id = tb_get_id($_page);
?>
<a href="<?php echo "$script?plugin=tb&amp;__mode=view&amp;tb_id=$tb_id" ?>" onClick="OpenTrackback(this.href); return false">TrackBack(<?php echo tb_count($_page) ?>)</a> | 
<?php } ?>

<?php
  if ($referer) {
?>
<a href="<?php echo "$script?plugin=referer&amp;page=$r_page" ?>">������󥯸�</a>
<?php } ?>
</div><!-- �� END id:trackback -->
<?php if ($related != '') { ?><!-- �� BEGIN id:related -->
<div id="related">
Link: <?php echo $related ?>
</div>
<?php } ?><!-- �� END id:related -->
<?php if ($attaches != '') { ?><!-- �� BEGIN id:attach -->
<div id="attach">
<?php echo $hr ?>
<?php echo $attaches ?>
</div>
<?php } ?><!-- �� END id:attach -->
</div><!-- �� END id:summary -->
</div><!-- ��END id:content -->
</div><!-- �� END id:wrap_content -->
<!-- ��sidebar�� ========================================================== -->
<div id="wrap_sidebar"><!-- ��BEGIN id:wrap_sidebar -->
<div id="sidebar">
<div id="search_form" class="bar"><!-- ��BEGIN id:search_form -->
<h2>����</h2>
<form action="<?php echo $script ?>?cmd=search" method="post">
<div>
	<input type="hidden" name="encode_hint" value="��" />
	<input type="text"  name="word" value="" size="20" />
	<input type="submit" value="����" /><br />
	<input type="radio" name="type" value="AND" checked="checked" id="and_search" /><label for="and_search">AND����</label>
  <input type="radio" name="type" value="OR" id="or_search" /><label for="or_search">OR����</label>
</div>
</form>
</div><!-- END id:search_form -->
<div id="page_action" class="bar"><!-- ��BEGIN id:page_action -->
<h2>�Խ����</h2>
<ul>
<?php if ($is_page) { ?>
	<li class="pa_reload"><a href="<?php echo "$script?$r_page" ?>">�����</a></li>
	<li class="pa_newpage"><a href="<?php echo "$script?plugin=newpage&amp;refer=$r_page" ?>">����</a></li>
	<li class="pa_edit"><a href="<?php echo $link_edit ?>">�Խ�</a></li>
<?php   if ($is_read and $function_freeze) { ?>
<?php     if ($is_freeze) { ?>
	<li class="pa_unfreeze"><a href="<?php echo $link_unfreeze ?>">�����</a></li>
<?php     } else { ?>
	<li class="pa_freeze"><a href="<?php echo $link_freeze ?>">���</a></li>
<?php     } ?>
<?php   } ?>
<?php   if ((bool)ini_get('file_uploads')) { ?>
	<li class="pa_attach"><a href="<?php echo $link_upload ?>">ź��</a></li>
<?php   } ?>
	<li class="pa_diff"><a href="<?php echo $link_diff ?>">��ʬ</a></li>
<?php } ?>
	<li class="pa_list"><a href="<?php echo $link_list ?>">����</a></li>
<?php if (arg_check('list')) { ?>
	<li class="pa_filelist"><a href="<?php echo $link_filelist ?>">�ե�����̾����</a></li>
<?php } ?>
	<li class="pa_serch"><a href="<?php echo $link_search ?>">ñ�측��</a></li>
	<li class="pa_whatnew"><a href="<?php echo $link_whatsnew ?>">�ǽ�����</a></li>
<?php if ($do_backup) { ?>
	<li class="pa_backup"><a href="<?php echo $link_backup ?>">�Хå����å�</a></li>
<?php } ?>
	<li class="pa_help"><a href="<?php echo $link_help ?>">�إ��</a></li>
</ul>
</div><!-- ��END id:page_action -->

<?php if (exist_plugin_convert('menu')) { ?><!-- ��BEGIN id:menubar -->
<div id="menubar" class="bar">
<?php echo do_plugin_convert('menu') ?>
</div>
<?php } ?><!-- ��END id:menubar -->

</div><!-- ��END id:sidebar -->
</div><!-- ��END id:wrap_sidebar -->
</div><!-- ��END id:main -->
<!-- �� Footer �� ========================================================== -->
<div id="footer"><!-- ��BEGIN id:footer -->
<div id="copyright"><!-- ��BEGIN id:copyright -->
 Site admin: <a href="<?php echo $modifierlink ?>"><?php echo $modifier ?></a><p />
 Powered by PukiWiki Plus! <?php echo S_VERSION ?> with PHP <?php echo PHP_VERSION ?>.
 HTML convert time: <?php echo $taketime ?> sec.
</div><!-- ��END id:copyright -->
</div><!-- ��END id:footer -->
<!-- �� END �� ============================================================= -->
</div><!-- ��END id:wrapper -->

</body>
</html>
