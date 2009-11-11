<?php

class Oops_Log_Error {
	public static function report(Oops_Error_Handler $errorHandler, $path) {
		$errors = '';
		foreach($errorHandler->getErrors() as $err)
			$errors .= "Oops-Error: $err\n";
		foreach($errorHandler->getWarnings() as $err)
			$errors .= "Oops-Warning: $err\n";
		foreach($errorHandler->getNotices() as $err)
			$errors .= "Oops-Notice: $err\n";
		foreach($errorHandler->getPhps() as $err)
			$errors .= "Php-errors: $err\n";

		if(!strlen($errors)) return;

		$logFile = new Oops_File($path . "/error.log");
		if($logFile->size > 50*1024) {
			if(file_exists($logFile->filename . ".1")) unlink($logFile->filename . ".1");
			$logFile->rename($logFile->filename . ".1");
			$logFile = new Oops_File($path . "/error.log");
		}
		
		$logFile->makeWriteable();
		$ft = fopen($logFile->filename, "a");

		$request = Oops_Server::getRequest();
		fputs($ft, "\n" . date("r") . "\n " . $request->getUrl() ."\n" . $errors);
		fclose($ft);
	}
}