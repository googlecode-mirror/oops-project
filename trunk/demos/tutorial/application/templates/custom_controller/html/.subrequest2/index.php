<div class="subrequest">
<h5>Subrequest to Custom_Controller</h5>
	<div class="indexnumber">Template call index number: <b><?=$this->_num?></b></div>
	<div class="tplname">called template name: <u><?=$this->_tplname?></u></i></div>
	<div class="tplfile">found template file: <u><?=$this->_tplfile?></u></i></div>

Custom controller translates query and router params into <code>$this->Data</code>
<br>Here's a list of query params:
<ul><?foreach($this->Data['request'] as $k=>$v) {
	echo "<li>$k => $v</li>";
}?></ul>
<br>Here's a list of server params:
<ul><?foreach($this->Data['server'] as $k=>$v) {
	echo "<li>$k => $v</li>";
}?></ul>
</div>