<?php
	echo $this->call("_layout/header.php");

?><div class="filter">
	<h2>Filter template</h2>
	<div class="indexnumber">Template call index number: <b><?php echo $this->_num?></b></div>
	<div class="tplname">called template name: <u><?php echo $this->_tplName?></u></div>
	<div class="tplfile">found template file: <u><?php echo $this->_tplFile?></u></div>

	<br>This is a filter template, use it to set page layout.<br>
	Template is addressed using View type (requested file extension), request path and request action.<br>
	<i>This one's location is <u><?php echo $this->_tplFile;?></u></i><br><br>
	If no template matching criterias found, content is being output directly.<br>
	<br>
	Controller content passed to this template is stored in <code>$this-&gt;Data</code>.<br>

	Here's a result of this code
	<code>
		&lt;?=$this-&gt;Data;?&gt;
	</code>
	data<?php echo $this->Data?>data

	<?php echo file_get_contents("oops://local/.subrequest//index.html?key=value+from+template");?>

</div>
<?php
	echo $this->call("_layout/footer.php");

