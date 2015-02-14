<?php
?>
<h3>Ticket Is Locked</h3>
<br/>
<div style="padding-left:1em;border:0;border-left:7px;border-color:#CCC; border-style:solid; margin-bottom:1em;">
This ticket is locked by another user.  
<br/>
If you wish to break this lock, click "Break Lock".  Click "Cancel" to go back home.
<br/>
Ticket Owner: <?= $response->ticketObj->getUsername(); ?>
<hr style="margin:7px;width:44em;"/>
<form method="POST" action="<?=m_appurl('cportal/ticket/breaklock');?>">
<input type="checkbox" name="take" value="on" id="chk-take" checked="checked"/> <label for="chk-take">Take Ownership?</label>
<br/>
<br/>
<input type="submit" name="sbmt-btn" value="Break Lock"/>
&nbsp; &nbsp;
<input type="submit" name="cncl-btn" value="Cancel"/>
<input type="hidden" name="id" value="<?=$response->ticketObj->getId();?>"/>
</form>
</div>
