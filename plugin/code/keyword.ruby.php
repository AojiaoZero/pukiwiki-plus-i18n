<?php
/**
 *�����������ե�����
 */

$switchHash["$"] = ESCAPE;            // $ �ϥ���������
$switchHash["'"] = NONESCAPE_LITERAL; // ' �ϥ��������פ��ʤ�ʸ�����ƥ��
$mkoutline = $option["outline"] = false; // �����ȥ饤��⡼���Բ� 

// ���������

$switchHash["#"] = COMMENT;	// �����Ȥ� # ������Ԥޤ� (�㳰����)
$switchHash["="] = COMMENT;	// �����Ȥ� =begin ���� =end �ޤ�
$switchHash["("] = COMMENT;	// �����Ȥ� (?# ���� ) �ޤ� (����ɽ����)
$code_comment = Array(
	"#" => Array(
		"/#(\n|[^{].*\n)/",
	),
		"=" => Array(
		"/=begin\n(.|\n)*=end\n/",
	),
		'(' => Array(
		"/\(\?\#.*\)/",
	)
);

$code_css = Array(
  'operator',		// ���ڥ졼���ؿ�
  'identifier',	// ����¾�μ��̻�
  'pragma',		// module, import �� pragma
  'system',		// �������Ȥ߹��ߤ��� __stdcall �Ȥ�
  );

$code_keyword = Array(
  //'operator',		// ���ڥ졼���ؿ�
  //'identifier',	// ����¾�μ��̻�
  	// ���

  	// ���湽ʸ�ط�
  	'for' => 2,
	'in' => 2,
  	'while' => 2,
  	'do' => 2,
  	'done' => 2,
	'each' => 2,
	'until' => 2,
	'yield' => 2,

	'BEGIN' => 2,
	'END' => 2,
	'begin' => 2,
	'end' => 2,
  	'if' => 2,
	'then' => 2,
  	'else' => 2,
  	'elsif' => 2,
	'unless' => 2,
  	'switch' => 2,
  	
  	'case' => 2,
	'break' => 2,
	'next' => 2,
	'redo' => 2,
	'retry' => 2,
  	'return' => 2,

	'and' => 2,
	'or' => 2,
	'not' => 2,
	'true' => 2,
	'false' => 2,


	// �ѿ������״ط�
  	
  	// ���饹��
  	'class' => 2,
  	'module' => 2,
  	'def' => 2,
  	'defined' => 2,
  	'undef' => 2,
  	'alias' => 2,
	'self' => 2,
	'super' => 2,
  	
  	// �㳰���� 
	'rescue' => 2,
	'ensure' => 2,
	'raise' => 2,

  //'pragma',		// module, import �� pragma
  	'include' => 3,
  	'require' => 3,
  //'system',		// �������Ȥ߹��ߤ��� __stdcall �Ȥ�
  );
?>