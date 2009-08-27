<div class="content">
	<h1>Target controller template</h1>
	<div class="indexnumber">Template call index number: <b><?php echo $this->_num?></b></div>
	<div class="tplname">called template name: <u><?php echo $this->_tplname?></u></div>
	<div class="tplfile">found template file: <u><?php echo $this->_tplfile?></u></div>

	<br>
	This is a constroller data presentation template<br>
	Template is addressed similar to filter but the first dir is controller class name<br>
	<i>This one's location is <u><?php echo __FILE__;?></u></i><br><br>
	<br>
	Controller data is stored in <code>$this-&gt;Data</code>.<br>

	Here's a dump of constroller result:<br>
	<code>
		<?php print_r($this->Data);?>
	</code>

	Illegal subrequest is: <?php echo file_get_contents("oops://local/.subrequest2/index.html?param1=value1&param2=value+2")?>
</div>
