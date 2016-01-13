<?php
/*
 Template Name: Newsletter
 *

*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

require "Drewm/MailChimp.php";


if ( !current_user_can('publish_posts') ) {
    echo 'Sorry, you don\'t have access to this page';
    exit();
   
}


$send='';
if(isset($_GET['send'])){
  $send=$_GET['send'];
}

//titol newsletter
$title="";
if(isset($_GET['title'])){
  $title=$_GET['title'];
}



  $options=get_option("pimpampum_newsletter_options");
  if($send=="final"){
    $list_id=$options['ok_list_id'];
  }else{
      $list_id=$options['test_list_id'];
  }

$MailChimp = new \Drewm\MailChimp($options['api_key']);


/*
default: veure resultat
send=''

send=test : a la llista de text
send=final : a la llista final

*/



if($send!="") ob_start();
?>
<style>
/* newsletter */

#newsletter-options {
position: fixed;
bottom: 0;
left: 0;
right: 0;
width: 100%;
background-color: white;
text-align: center;
padding: 20px; 
box-shadow: 0 0 12px rgba(0,0,0,0.3);
}

input[type="text"] {
border: 1px solid #666;
font-size: 15px;
padding: 6px 10px;
}

input[type="button"] {
font-size: 18px;
padding: 10px 25px;
border: none;
color: #fff;
font-weight: bold;
background-color: #000;
margin: 0 10px;
}

input[type="button"]:hover {
background-color: #a31400;
cursor: pointer;
}




</style>
<?php

   // load the file if exists
     if ( $overridden_template = locate_template( 'mailchimp-templates/mailchimp-main-template.php' ) ) {
   // locate_template() returns path to file
   // if either the child theme or the parent theme have overridden the template
   load_template( $overridden_template );
 
 } else {
   // If neither the child nor parent theme have overridden the template,
   // we load the template from the 'templates' sub-directory of the directory this file is in
   load_template( dirname( __FILE__ ) . '/mailchimp-templates/mailchimp-main-template.php' );
  
 }


?>


<?php
if($send==""):
?>

<div id="newsletter-options" >
<form id="myform" method="get" action="<?php print get_permalink( 2530 );?>">
<?php print __("Title","mt")?>: <input type="text" name="title" size="48" placeholder="Your campaign title" >
<input type="hidden" id="send" name="send" value="test">
<input type="button" onclick="dosend(false)" value="<?php print __("Send to the TEST list","mt")?>">
<input type="button" onclick="dosend(true)" value="<?php print __("Send to final list","mt")?>">
</form>
</div>

<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
<script>

$(document).ready(function(){

});

function dosend(final){
  if(final){
    if(!confirm("Are you sure?")){
      return;
    }
    $('#send').val("final");
  }else{
    $('#send').val("test");
  }
    $('#myform').submit();

}

</script>

<?php
endif;
?>

    </body>
</html>

<?php 


if($send!=""){

  $content = ob_get_clean();


  //$content= $doc->saveHtml();



if($send!="final") $title="[ TEST ] ".$title;

$res=$MailChimp->call('campaigns/create', array(
'type'=>'regular',
  'options'=> array( 
'list_id'=>$list_id,
'subject'=>$title, //todo canviar
'from_email'=>$options['from_email'],
'from_name'=>$options['from_name'],
//'from_email'=>'dani@pimpampum.net',
//'from_name'=>'Comunicació CJB',

),
 'content'=> array( 
'html'=>$content,
),
  ));

if(!isset($res['archive_url'])){
  print __("Error sending the newsletter","mt");
}else{ 
  print "<strong>".__("Newsletter successfully sent!","mt")."</strong>  ";
  print "<a target='result' href='".$res['archive_url']."'>".$res['archive_url']."</a>";
  print "<br>Debug information:<pre>";
  print_r($res);
  print "</pre>";
}

if(!isset($res['id'])){
  print __("Error sending the campaign!","mt");
  print "<pre>";
  print_r($res);
  print "</pre>";
}else{ 

  $cid=$res['id'];

  $res=$MailChimp->call('campaigns/send', array(
    'cid'=>$cid));
  }

}
