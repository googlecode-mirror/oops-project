<html>
<head>
<title>The page title</title>
<link rel="stylesheet" type="text/css" href="/css/s.css" />
</head>

<body>
<div class="layout" id="header">
	<h3>Layout header template</h3>
	<div class="indexnumber">Template call index number: <b><?php echo $this->_num?></b></div>
	<div class="tplname">called template name: <u><?php echo $this->_tplname?></u></div>
	<div class="tplfile">found template file: <u><?php echo $this->_tplfile?></u></div>
</div>