<?php
/**
 * make �����������ե�����
 * �Իظ��⡼����
 */


// ���̻ҳ���ʸ��
for ($i = ord("a"); $i <= ord("z"); $i++)
	 $switchHash[chr($i)] = POST_IDENTIFIRE;
for ($i = ord("A"); $i <= ord("Z"); $i++)
	 $switchHash[chr($i)] = POST_IDENTIFIRE;
$switchHash["."] = POST_IDENTIFIRE;
$post_identifire = ':';

	 
$switchHash["\t"] = IDENTIFIRE_CHAR;   // tab
//$switchHash["."]  = IDENTIFIRE_CHAR;   // 
$mkoutline = $option["outline"] = false; // �����ȥ饤��⡼���Բ� 

// ���������
$switchHash["#"] = COMMENT_CHAR;	// �����Ȥ� # ������Ԥޤ�

$code_css = Array(
  'operator',		// ���ڥ졼���ؿ�
  'identifier',	// ����¾�μ��̻�
  'pragma',		// module, import �� pragma
  'system',		// �������Ȥ߹��ߤ��� __stdcall �Ȥ�
  'execute'       // �¹�̿��
  );

$code_keyword = Array(
  'else' => 2,
  'endif' => 2,
  'if' => 2,
  'ifdef' => 2,
  'ifeq' => 2,
  'ifndef' => 2,
  'ifneq' => 2,
  'include' => 2,
  'sinclude' => 2,

  "\t" => 5,
  '.' => 3,

);
?>