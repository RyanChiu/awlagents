<?php
// Programmed by Reza Salehi - zaalion@yahoo.com - Free for non-commercial use. , Nov 2005
class dataGrid
{
	var $dataSource;
	var $dataKeyField;
	var $nextPage;
	var $nextPageLable;
	var $colNum;
	var $fieldsToShow;
	var $rowColor;	
	var $width;
	//
	function dataGrid($dataSource, $dataKeyField, $fieldsToShow, $nextPage, $nextPageLable, $rowColor, $width)
	{
		$this->dataSource=$dataSource;
		$this->dataKeyFeild=$dataKeyField;
		$this->nextPage=$nextPage;
		$this->nextPageLable=$nextPageLable;
		$this->fieldsToShow=$fieldsToShow;
		$this->colNum=sizeof($this->fieldsToShow);
		$this->rowColor=$rowColor;
		$this->width=$width;
	}
	//
	function create()
	{
		$rowColor=false;
		print("<table cellpadding=1 cellspacing=1 width=$this->width style='border: 1px solid'>");		
		print("<tr>");
		for($i=0; $i<$this->colNum; $i++)
		{
			print("<td>");
			print($this->fieldsToShow[$i]);
			print("</td>");
		}
		//
		if($this->nextPage!=NULL) print("<td>".$this->nextPageLable."</td>");
		//
		print("</tr>");
		//
		while($row=mysql_fetch_array($this->dataSource))
		{
			$rowColor=!$rowColor;
			if($rowColor)
				print("<tr bgcolor=".$this->rowColor.">");
			else
				print("<tr>");
			for($i=0; $i<$this->colNum; $i++)
			{
				print("<td>");
				print($row[$this->fieldsToShow[$i]]);
				print("</td>");
			}
			//
			if($this->nextPage!=NULL) print("<td><a href=".$this->nextPage."?".$this->dataKeyFeild."=".$row[$this->dataKeyFeild]
			." target=blank>".$this->nextPageLable."</a></td>");
			//
			print("</tr>");
		}
		print("</table><div style='height: 2px;'></div>");
	}
}
//

?>