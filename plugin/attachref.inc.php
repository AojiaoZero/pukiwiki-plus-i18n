<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: attachref.inc.php,v 0.14.1 2003/10/08 04:10:29 sha/miko Exp $
//

/*
*�ץ饰���� attachref
 ���ξ��ź�դ��롣attach & ref

*Usage
 &attachref;
 &attachref([<file>][,<ref options>][,button]);

*�ѥ�᡼��
-<file>: attach����ȼ�ưŪ���ɲä���롣�ǽ餫��񤤤Ƥ����Ƥ�褤��
-<ref options>: &ref;�Ѥΰ�����
-button: [attach]�Τ褦�ʥ�󥯤Ǥʤ���<form></form>�Υܥ���ˤ��롣

*ư��
(1)&attachref;���ɲä���ȡ�[attach]�ܥ���ɽ������롣
(2)[attach]�ܥ���򲡤��ȡ����åץ����ɥե����ब�����ơ����ꤷ���ե�����
  ��ź�դǤ��롣
(3)ź�դ��줿�ե������&ref(...);�ǻ��Ȥ����褦��Ž���դ����롣
(4)���Υե������������ȡ�"file not found"��[attach]�ܥ���ɽ������롣
(5)(4)�ΤȤ��ˡ����Υե����뤬�ɲä����ȺƤ����褹�롣

*/
// max file size for upload on PHP(PHP default 2MB)
ini_set('upload_max_filesize','2M');

// max file size for upload on script of PukiWiki(default 1MB)
define('MAX_FILESIZE',1000000);

// �����Ԥ�����ź�եե�����򥢥åץ����ɤǤ���褦�ˤ���
define('ATTACHREF_UPLOAD_ADMIN_ONLY',FALSE); // FALSE or TRUE
// ���åץ�����/������˥ѥ���ɤ��׵᤹��(ADMIN_ONLY��ͥ��)
define('ATTACHREF_PASSWORD_REQUIRE',FALSE); // FALSE or TRUE


// upload dir(must set end of /) attach.inc.php�ȹ�碌��
define('ATTACHREF_UPLOAD_DIR','./attach/');


function plugin_attachref_init()
{
	$messages = array(
		'_attachref_messages' => array(
			'btn_submit'    => '[ź��]',
			'msg_title'     => 'Attach and Ref to $1',
			'msg_title_collided' => '$1 �ǡڹ����ξ��ۤ͡������ޤ���',
			'msg_collided'  => '���ʤ����ե������ź�դ��Ƥ���֤ˡ�¾�οͤ�Ʊ���ڡ����򹹿����Ƥ��ޤä��褦�Ǥ���<br />
�ե����뤬�㤦���֤���������Ƥ��뤫�⤷��ޤ���<br />'
		),
	);
	set_plugin_messages($messages);
}
function plugin_attachref_inline()
{
	global $script,$vars,$digest;
	global $_attachref_messages;
	static $numbers = array();
	static $no_flag = 0;
	
	if (!array_key_exists($vars['page'],$numbers))
	{
		$numbers[$vars['page']] = 0;
	}
	$attachref_no = $numbers[$vars['page']]++;
	
	//�����
	$ret = '';
	$dispattach = 1;
	$button = 0;

	$args = func_get_args();
    $btn_text = array_pop($args);
    $btn_text = $btn_text ? $btn_text : $_attachref_messages['btn_submit'];
        
    $options = array();
    foreach ( $args as $opt ){
	    if ( $opt === 'button' ){
	        $button = 1;
	    }
	    else if ( $opt === 'number' ){
		$no_flag = 1;
	    }
	    else if ( $opt === 'nonumber' ){
		$no_flag = 0;
	    }
	    else {
	        array_push($options, $opt);
	    }
	}
    if ( $no_flag == 1 ) $btn_text .= "[$attachref_no]";
	$args = $options;
	if ( count($args) and $args[0]!='' )
	{
		require_once(PLUGIN_DIR."ref.inc.php");
	    $params = plugin_ref_body($args,$vars['page']);
	    if ($params['_error'] != '') {
		$ret = $params['_error'];
		$dispattach = 1;
	    }
	    else
	    {
		$ret = $params['_body'];
		$dispattach = 0;
	    }
	}
	if ( $dispattach ) {
	    //XSS�ȼ������� - ���������褿�ѿ��򥨥�������
	    $s_args = trim(join(",", $args));
	    if ( $button ){
			$s_args .= ",button";
			$f_page = htmlspecialchars($vars['page']);
			$f_args = htmlspecialchars($s_args);
			$ret = <<<EOD
  <form action="$script" method="post">
  <div>
  <input type="hidden" name="encode_hint" value="��" />
  <input type="hidden" name="attachref_no" value="$attachref_no" />
  <input type="hidden" name="attachref_opt" value="$f_args" />
  <input type="hidden" name="digest" value="$digest" />
  <input type="hidden" name="plugin" value="attachref" />
  <input type="hidden" name="refer" value="$f_page" />
  $ret
  <input type="submit" value="$btn_text" />
  </div>
  </form>
EOD;
	    }
	    else {
			$f_btn_text = preg_replace('/<[^<>]+>/','',$btn_text);
//		echo '[debug]btn=',$f_btn_text;
			$f_page = rawurlencode($vars['page']);
			$f_args = rawurlencode($s_args);
			$ret = <<<EOD
  $ret<a href="$script?plugin=attachref&amp;attachref_no=$attachref_no&amp;attachref_opt=$f_args&amp;refer=$f_page&amp;digest=$digest" title="$f_btn_text">$btn_text</a>
EOD;
	    }
	}
	return $ret;
}
function plugin_attachref_action()
{
	global $script,$vars;
	global $_attachref_messages;
	global $html_transitional;


	//����ͤ�����
	$retval['msg'] = $_attachref_messages['msg_title'];
	$retval['body'] = '';
	
	if (array_key_exists('attach_file',$_FILES)
		and array_key_exists('refer',$vars)
		and is_page($vars['refer']))
	{
		$file = $_FILES['attach_file'];
		$attachname = $file['name'];
		$filename = preg_replace('/\..+$/','', $attachname,1);

		//���Ǥ�¸�ߤ�����硢 �ե�����̾��'_0','_1',...���դ��Ʋ���(��©)
		$count = '_0';
		while (file_exists(ATTACHREF_UPLOAD_DIR.encode($vars['refer']).'_'.encode($attachname)))
		{
			$attachname = preg_replace('/^[^\.]+/',$filename.$count++,$file['name']);
		}
		
		$file['name'] = $attachname;
		
		require_once(PLUGIN_DIR."attach.inc.php");
		if (!exist_plugin('attach') or !function_exists('attach_upload'))
		{
			return array('msg'=>'attach.inc.php not found or not correct version.');
		}
		$pass = array_key_exists('pass',$vars) ? md5($vars['pass']) : NULL;
	    $retval = attach_upload($file,$vars['refer'],$pass);
		if ($retval['result'] == TRUE)
		{
			$retval = attachref_insert_ref($file['name']);
		}
	}
	else
	{
		$retval = attachref_showform();
		// XHTML 1.0 Transitional
		$html_transitional = TRUE;
	}
	return $retval;
}

function attachref_insert_ref($filename)
{
	global $script,$vars,$now,$do_backup;
	global $_attachref_messages;
	
	$ret['msg'] = $_attachref_messages['msg_title'];
	
	$args = split(",", $vars['attachref_opt']);
	if ( count($args) ){
	    $args[0] = './' . $filename;//array_shift,unshift�ä��פ���ˤ����
	    $s_args = join(",", $args);
	}
	else {
	    $s_args = './' . $filename;
	}
	$msg = "&attachref($s_args)";
	
	$refer = $vars['refer'];
	$digest = $vars['digest'];
	$postdata_old = get_source($refer);
	$thedigest = md5(join('',$postdata_old));

	$postdata = '';
	$attachref_ct = 0; //'#attachref'�νи����
	$attachref_no = $vars['attachref_no'];
	$skipflag = 0;
	foreach ($postdata_old as $line)
	{
	    if ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//' ){
		$postdata .= $line;
		continue;
	    }
	    $ct = preg_match_all('/&attachref(?=[({;])/',$line, $out);
	    if ( $ct ){
		for($i=0; $i < $ct; $i++){
		    if ($attachref_ct++ == $attachref_no ){
			$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/',$msg.'$2;',$line,1);
			$skipflag = 1;
			break;
		    }
		    else {
			$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/','&___attachref$1$2___;',$line,1);
		    }
		}
		$line = preg_replace('/&___attachref(\([^(){};]*\))?(\{[^{}]*\})?___;/','&attachref$1$2;',$line);
//		$postdata .= "<hr />$ct|$attachref_no|$line";
	    }
	    $postdata .= $line;
	}
	
	// �����ξ��ͤ򸡽�
	if ( $thedigest != $digest )
	{
		$ret['msg'] = $_attachref_messages['msg_title_collided'];
		$ret['body'] = $_attachref_messages['msg_collided'];
	}
/*
	$postdata .= "<hr />$refer, " . join('/',array_keys($vars)) . ", " . join("/",array_values($vars)) . ", s_args=$s_args";
	$ret['body'] = $postdata;
*/
	page_write($vars['refer'],$postdata);
	
	return $ret;
}
//���åץ����ɥե������ɽ��
function attachref_showform()
{
	global $vars;
	global $_attach_messages;
	
	$vars['page'] = $vars['refer'];
	$body = ini_get('file_uploads') ? attachref_form($vars['page']) : 'file_uploads disabled.';
	
	return array('msg'=>$_attach_messages['msg_upload'],'body'=>$body);
}
//���åץ����ɥե�����
function attachref_form($page)
{
	global $script,$vars;
	global $_attach_messages;
	
	$s_page = htmlspecialchars($page);

	$f_digest = array_key_exists('digest',$vars) ? $vars['digest'] : '';
	$f_no = (array_key_exists('attachref_no',$vars) and is_numeric($vars['attachref_no'])) ?
		$vars['attachref_no'] + 0 : 0;


	if (!(bool)ini_get('file_uploads'))
	{
		return "";
	}
	
	$maxsize = MAX_FILESIZE;
	$msg_maxsize = sprintf($_attach_messages['msg_maxsize'],number_format($maxsize/1000)."KB");

	$pass = '';
	if (ATTACHREF_PASSWORD_REQUIRE or ATTACHREF_UPLOAD_ADMIN_ONLY)
	{
		$title = $_attach_messages[ATTACHREF_UPLOAD_ADMIN_ONLY ? 'msg_adminpass' : 'msg_password'];
		$pass = '<br />'.$title.': <input type="password" name="pass" size="8" />';
	}
	return <<<EOD
<form enctype="multipart/form-data" action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="attachref" />
  <input type="hidden" name="pcmd" value="post" />
  <input type="hidden" name="attachref_no" value="$f_no" />
  <input type="hidden" name="attachref_opt" value="{$vars['attachref_opt']}" />
  <input type="hidden" name="digest" value="$f_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="max_file_size" value="$maxsize" />
  <span class="small">
   $msg_maxsize
  </span><br />
  {$_attach_messages['msg_file']}: <input type="file" name="attach_file" />
  $pass
  <input type="submit" value="{$_attach_messages['btn_upload']}" />
 </div>
</form>
EOD;
}
?>