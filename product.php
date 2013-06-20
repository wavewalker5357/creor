<?php

require_once 'library/core.php';
if(ctype_digit($_GET['id']) && mysql_num_rows(db::sql('select','product','id',$_GET['id']))>0){
$product=new product($_GET['id']);
if($product->get_active()!='1'){
header('Location: http://'.$_SERVER['HTTP_HOST'].page::baseurl().'/error');
//$page->clear('<div class="clear">Stránka nebyla nalezena.</div>');
//$page->disable();
}

$page->meta('<script type="text/javascript" src="'.page::baseurl().'/support/jquery.scrollable.js"></script>');
$page->meta('<script type="text/javascript">
$(function(){

$(\'input\').focus(function() {
var $el = $(this).parents(\'form\').find(\'input[type="submit"]\');
$(\'#presunovac\').insertBefore($el);
});

$(\'.comment span.zobrazit\').click(function(){
$(this).closest(\'div.comment\').find(\'div\').toggle(\'medium\');
});
$(\'#scrollable\').scrollable({
next: \'#right\', 
prev: \'#left\',
size: 3 
});
$(\'#scrollable .items img\').click(function(){
$(\'#images a\').addClass(\'erase\');
$(\'#images a#\'+$(this).attr(\'id\')+\'-big\').removeClass(\'erase\');
});
});
</script>');

$product->set_description(htmlspecialchars_decode($product->get_description()));
$page->set_title($product->get_name().' - '.category::complete_name($product->get_category(),', '));
$page->set_description(editor::example($product->get_description()));
$page->set_keywords($product->get_name().', '.category::complete_name($product->get_category(),', '));
$page->set_navigation(category::last_href_name($product->get_category()).' '.category::SEPARATOR.' <a href="'.product::url($product->get_id()).'">'.$product->get_name().'</a>');
$page->set_actual($product->get_category());
for($parent=$product->get_category();$parent!=0;$parent=category::get_parent($parent))$page->onload('tree_expand(\'menu_'.$parent.'\');'.$page->get_onload());

$page->write('<div class="detail">');


$page->write('<table summary="obrázky produktu" class="obrazky" cellspacing="0">
<tr><td colspan="3" id="images">');
$sql=db::sql('select','picture','id','product="'.$product->get_id().'"');
if(mysql_num_rows($sql)>1){
$i=0;
while($id=mysql_fetch_object($sql)){
$picture=new picture($id->id);
$page->write('<a class="thickbox'.($i>0 ? ' erase' : '').'" id="img'.$i.'-big" rel="lightbox" href="'.page::baseurl().'/product/'.$picture->get_bigimg().'" title="'.$picture->get_name().'"><img src="'.page::baseurl().'/product/'.$picture->get_img().'" alt="'.$picture->get_name().'" title="'.$picture->get_name().'" /></a>');
$i++;
}
}else{
$picture=$product->picture();
if($picture)$page->write('<a class="thickbox" rel="lightbox" href="'.page::baseurl().'/product/'.$picture->get_bigimg().'" title="'.$picture->get_name().'"><img src="'.page::baseurl().'/product/'.$picture->get_img().'" alt="'.$picture->get_name().'" title="'.$picture->get_name().'" /></a>');
else $page->write('<img src="'.page::baseurl().'/images/default.png" alt="'.$product->get_name().'" title="'.$product->get_name().'" />');
}
$page->write('</td></tr>');
$sql=db::sql('select','picture','id','product="'.$product->get_id().'"');
if(mysql_num_rows($sql)>1){
$page->write('<tr class="vyber"><td>
<img src="'.page::baseurl().'/images/scroll_left.png" alt="&lt;" id="left" />
</td><td class="scrollable"><div id="scrollable"><div class="items">');
$i=0;
while($id=mysql_fetch_object($sql)){
$picture=new picture($id->id);
$page->write('<img src="'.page::baseurl().'/product/'.$picture->get_mini().'" alt="'.$picture->get_name().'" id="img'.$i.'" />');
$i++;
}

$page->write('</div></div></td><td>
<img src="'.page::baseurl().'/images/scroll_right.png" alt="&gt;" id="right" />
</td></tr>');
}
$page->write('</table>');


$page->write('<div class="blok">
<div class="rohy-horni"></div>
<div class="ceny">
<h1>'.$product->get_name().'</h1>');
$page->write('<strong>Obtížnost:</strong> ');

for($i=1;$i<=(int)$product->get_difficulty();$i++){
$page->write('<img src="'.page::baseurl().'/images/hvezdicka.png" alt="*" title="Obtížnost '.$product->get_difficulty().'" />');
}
for(;$i<=6;$i++){
$page->write('<img src="'.page::baseurl().'/images/hvezdicka-prazdna.png" alt="*" title="Obtížnost '.$product->get_difficulty().'" />');
}

$page->write('<br />
<span class="sdph">Cena: '.$product->get_dph().',-Kč</span> vč. DPH<br />
</div>
<div class="rohy-dolni"></div>
</div>');

$page->write('<div class="blok">
<div class="rohy-horni"></div>
<div class="produkt-info">
<table summary="informace o produktu" cellspacing="0">');
$page->write('<tr><td><strong>Výrobce:</strong></td><td>'.$product->get_maker().'</td></tr>');
$page->write('<tr><td><strong>Kód:</strong></td><td>'.$product->get_code().'</td></tr>');
$page->write('<tr><td><strong>Materiál:</strong></td><td>'.$product->get_material().'</td></tr>');
$page->write('<tr><td><strong>Země původu:</strong></td><td>'.$product->get_origin().'</td></tr>');
$page->write('<tr><td><strong>Rozměry:</strong></td><td>'.$product->get_sizes().'</td></tr>');
$page->write('<tr><td><strong>Záruka:</strong></td><td>'.$product->get_guaranty().'</td></tr>');
$page->write('<tr><td><strong>Dostupnost:</strong></td><td class="dostupnost">'.($product->get_stock()>0 ? 'skladem' : $product->get_availability()).'</td></tr>');
$page->write('</table>
</div>
<div class="rohy-dolni"></div>
</div>

<div class="velke-buttony">

<div class="blok">
<div class="rohy-horni"></div>
<a href="javascript:toggle_form(\'dotaz_form\');" class="zeptat">Zeptejte se prodejce</a>
<div class="rohy-dolni"></div>
</div>

<a href="'.page::baseurl().'/'.URL_CART.'?add='.$product->get_id().'" class="koupit" rel="nofollow">Koupit</a>

</div>

<div class="velky-blok" id="dotaz_form">
<div class="rohy-horni"></div>');

if(empty($_POST))$page->onload('$(\'#dotaz_form\').css(\'display\',\'none\');'.$page->get_onload());

//phpinfo();
//var_export($_POST);

$form=new form();
$form->add('Jméno','text','name',true);
$form->add('Příjmení','text','surname',true);
$form->add('E-mail','text/email','email',true);
$form->add('Telefon','text','phone');
$form->add('Text zprávy','textarea','text',true);
$form->add('Odeslat','submit','submit_question');

if ( isset( $_POST['submit_question'] ) ) {

require_once('library/recaptchalib.php');
$privatekey = "6LdOHuASAAAAAN58F3qNC4pT1-EpvaZPqlxcxD8w";
$resp = recaptcha_check_answer ($privatekey,
$_SERVER["REMOTE_ADDR"],
$_POST["recaptcha_challenge_field"],
$_POST["recaptcha_response_field"]);

if (!$resp->is_valid) {
// What happens when the CAPTCHA was entered incorrectly
die ("Kód byl špatně opsán. Vraťte se zpět a zkuste to znovu." .
"(Důvod říká: " . $resp->error . ")");
}
  }

$control=(!isset($_POST['submit_question']) ? '' : $form->control($_POST));
$error=(is_array($control) ? '' : $control);

if($error!='' || !isset($_POST['submit_question'])){
$page->write($error=='' ? '' : '<div class="error">'.$error.'</div>');
$page->write(form::start($_SERVER['REDIRECT_URL'],'post','multipart/form-data','dotaz'));
$page->write($form->table());     
$page->write(form::finish());
}else{
$question=new question();
$question->array_construct($control);
$question->set_product($product->get_id());
$question->db_insert();

$seller_head=
'MIME-Version: 1.0'.NL.
'Content-transfer-encoding: 8bit'.NL.
'Content-type: text/html; charset=UTF-8'.NL.
'From: "Informace CREOR.cz" <'.$question->get_email().'>'.NL.
'Reply-To: '.$question->get_email();
$seller='
<p>Přijali jsme dotaz na CREOR.cz.<br />
Vyplněné údaje:<br /></p>
<table>
<tr><td>Jméno:</td><td>'.$question->get_name().'</td></tr>
<tr><td>Příjmení:</td><td>'.$question->get_surname().'</td></tr>
<tr><td>E-mail:</td><td>'.$question->get_email().'</td></tr>
<tr><td>Telefon:</td><td>'.$question->get_phone().'</td></tr>
<tr><td>Produkt:</td><td>'.$product->get_name().'</td></tr>
<tr><td>Text zprávy:</td><td>'.$question->get_text().'</td></tr>
<tr><td>IP adresa:</td><td>'.$_SERVER['REMOTE_ADDR'].'</td></tr>
</table>
';
if(@mail(EMAIL_DOTAZ,'Dotaz - CREOR.cz',$seller,$seller_head))$page->write('<div class="success">Váš dotaz byl odeslán.</div>');
else $page->write('<div class="error">Nepodařilo se odeslat e-mail.</div>');
}


/*
<form action="'.page::baseurl().'/question?product='.$product->get_id().'" method="post">
<table summary="Formulář pro dotaz" cellspacing="0">
<tr><td>
<strong>E-mail:</strong><input name="email" />
</td><td>
<strong>Předmět:</strong><input name="subject" />
</td></tr>
<tr><td colspan="2">
<strong>Text zprávy:</strong>
<textarea cols="56" rows="4" name="text"></textarea>
</td></tr>
<tr><td colspan="2">
<input type="submit" value="Odeslat" class="submit" />
</td></tr>
</table>
</form>
*/
$page->write('<div class="rohy-dolni"></div>
</div>

<div class="cara"></div>');


if($product->get_description()!=''){
$page->write('<h2>Popis produktu</h2>
<div class="velky-blok">
<div class="rohy-horni"></div>
<div class="editor">');
$page->write($product->get_description());
$page->write('</div>
<div class="rohy-dolni"></div>
</div>');
}




$page->write('<h2>Komentáře</h2>');

$page->write('<div class="comment_links">
<a href="javascript:toggle_form(\'comment_form\');">Přidat komentář</a>');
if(db::sql('count','comment','product="'.$product->get_id().'"')>0)$page->write('<a href="?comments">Zobrazit všechny komentáře</a>');
$page->write('<br class="clear" />
</div>');

if(empty($_POST))$page->onload('$(\'#comment_form\').css(\'display\',\'none\');'.$page->get_onload());

$form=new form();
if (isset($_POST['name']))
$form->add('Jméno','text','name',true, $_POST['name']);
else
$form->add('Jméno','text','name',true);

if (isset($_POST['subject']))
$form->add('Předmět','text','subject',true, $_POST['subject']);
else
$form->add('Předmět','text','subject',true);

if (isset($_POST['email']))
$form->add('E-mail','text/email','email',false, $_POST['email']);
else
$form->add('E-mail','text/email','email',false);

if (isset($_POST['text']))
$form->add('Text','textarea','text',true, $_POST['text']);
else
$form->add('Text','textarea','text',true);

if (isset($_POST['submit_comment']))
$form->add('Odeslat','submit','submit_comment', $_POST['submit_comment']);
else
$form->add('Odeslat','submit','submit_comment');

if ( isset( $_POST['submit_comment'] ) ) {

require_once('library/recaptchalib.php');
$privatekey = "6LdOHuASAAAAAN58F3qNC4pT1-EpvaZPqlxcxD8w";
$resp = recaptcha_check_answer ($privatekey,
$_SERVER["REMOTE_ADDR"],
$_POST["recaptcha_challenge_field"],
$_POST["recaptcha_response_field"]);


if (!$resp->is_valid) {
// What happens when the CAPTCHA was entered incorrectly
die ("Kód byl špatně opsán. Vraťte se zpět a zkuste to znovu." .
"(Důvod říká: " . $resp->error . ")");
}
  }
$control=(!isset($_POST['submit_comment']) ? '' : $form->control($_POST));
$error=(is_array($control) ? '' : $control);

if($error!='' || !isset($_POST['submit_comment'])){
$page->write('<div id="comment_form">');
$page->write($error=='' ? '' : '<div class="error">'.$error.'</div>');
$page->write(form::start($_SERVER['REDIRECT_URL'],'post','multipart/form-data'));
require_once('library/recaptchalib.php');
$publickey = "6LdOHuASAAAAAGxdBb8c20bgUk7QVCPC2Qx88a39 "; // you got this from the signup page
$page->write($form->table('<div id="presunovac">' . recaptcha_get_html($publickey) . '</div>'));
$page->write('</div>');
$page->write(form::finish());
$page->write('</div>');
}else{
$comment=new comment();
$comment->array_construct($control);
var_export($control);

$comment->set_product($product->get_id());
$comment->set_ip($_SERVER['REMOTE_ADDR']);
$comment->set_time(db::time());
$comment->db_insert();
}

if(isset($_GET['comments'])){

$sql=db::sql('select','comment','id','product="'.$product->get_id().'"','time DESC');
if(mysql_num_rows($sql)>0){
while($id=mysql_fetch_object($sql)){
$comment=new comment($id->id);
$page->write('
<div class="comment">
<span><strong>Autor:</strong> '.$comment->get_name().'</span>
<span><strong>Předmět:</strong> '.$comment->get_subject().'</span>
<span class="zobrazit">Zobrazit</span><br />
<div>
<span><strong>Čas:</strong> '.cas($comment->get_time()).'</span>
'.($comment->get_email()=='' ? '' : '<span><strong>Email:</strong> '.$comment->get_email().'</span>').'
<br />
<p>'.$comment->get_text().'</p>
</div>
</div>
');

}
}

}else{

$sql=db::sql('select','comment','id','product="'.$product->get_id().'"','time DESC','5');
if(mysql_num_rows($sql)>0){
//zabalit/rozbalit dle postu
while($id=mysql_fetch_object($sql)){
$comment=new comment($id->id);
//'product','subject','name','email','text','ip','time'
$page->write('
<div class="comment">
<span><strong>Autor:</strong> '.$comment->get_name().'</span>
<span><strong>Předmět:</strong> '.$comment->get_subject().'</span>
<span class="zobrazit">Zobrazit</span><br />
<div class="erase">
<span><strong>Čas:</strong> '.cas($comment->get_time()).'</span>
'.($comment->get_email()=='' ? '' : '<span><strong>Email:</strong> '.$comment->get_email().'</span>').'
<br />
<p>'.$comment->get_text().'</p>
</div>
</div>
');

}
}

}

$page->write('</div>');

$sql=db::sql('select','product','id','`category`="'.$product->get_category().'" && `active`="1" && `id`!="'.$product->get_id().'"','RAND()','4');
if(mysql_num_rows($sql)>0){
$page->write('<div class="cara"></div>');
$page->write('<strong class="souvisejici">Související produkty</strong>');
$page->write('<div class="vypis">');
while($random_product=mysql_fetch_object($sql)){
$page->write(product_write($random_product->id));
}
$page->write('</div>');
}

}else{
header('Location: http://'.$_SERVER['HTTP_HOST'].page::baseurl().'/error');
//$page->clear('<div class="clear">Produkt nebyl nalezen.</div>');
//$page->disable();
}

echo $page->tostring();
?>
