<?php

class Oops_Sql_Selector_Exception extends Exception {
	const NoTable = 1;
	const NoPrimaryKey = 2;
	const UnexpectedValueType = 4;
	const UnexpectedCompareType = 8;
}