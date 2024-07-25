<?php

namespace generic\tsvConverter\classes;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;

class Helpers
{
	public static function  searchLocalisations($key, $input, $intend, $tag = null, $flags = null)
	{
		global $locales;

		if ($tag == "") $tag = $key;

		$nodes = "";
		$pattern = "/:" . $key . "/";
		$values = array_intersect_key($input, array_filter(array_flip(preg_grep($pattern, array_keys($input), $flags ?? 0))));

		foreach ($values as $keyval => $value) {
			if ($value != "") {
				$shortLocale = explode(":", $keyval);
				if (strpos($value, "\n") !== false || strpos($value, "&") !== false || strpos($value, "<") !== false || strpos($value, ">") !== false) {
					$value = "<![CDATA[" . nl2br($value) . "]]>";
				}
				for ($i = 0; $i < $intend; $i++) $nodes .= "\t";
				$nodes .= "<" . $tag . " locale=\"" . $locales[$shortLocale[0]] . "\">" . $value . "</" . $tag . ">\r\n";
			}
		}

		return $nodes;
	}

	public static function  searchTaxonomyLocalisations($key, $key_singular, $input, $intend, $flags = 0)
	{
		global $locales;

		$nodes = "";
		$intend_string = "";
		for ($i = 0; $i < $intend; $i++) $intend_string .= "\t";
		$pattern = "/:" . $key . "/";
		$values = array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));

		foreach ($values as $keyval => $value) {
			if ($value != "") {
				$shortLocale = explode(":", $keyval);
				$nodes .= $intend_string . "<" . $key . " locale=\"" . $locales[$shortLocale[0]] . "\">\r\n";
				$subvalues = explode(";", $value);
				foreach ($subvalues as $subvalue) {
					$nodes .= $intend_string . "\t<" . $key_singular . "><![CDATA[" . trim($subvalue) . "]]></" . $key_singular . ">\r\n";
				}
				$nodes .= $intend_string . "</" . $key . ">\r\n";
			}
		}

		return $nodes;
	}

	public static function  createArray($sheet)
	{
		$highestrow = $sheet->getHighestRow();
		$highestcolumn = $sheet->getHighestColumn();
		$columncount = Coordinate::columnIndexFromString($highestcolumn);
		$headerRow = $sheet->rangeToArray('A1:' . $highestcolumn . "1");
		$header = $headerRow[0];
		array_unshift($header, "");
		unset($header[0]);
		$array = array();
		for ($row = 2; $row <= $highestrow; $row++) {
			$a = array();
			for ($column = 1; $column <= $columncount; $column++) {

				if (strpos($header[$column], "abstract") !== false) {
					if ($sheet->getCellByColumnAndRow($column, $row)->getValue() instanceof RichText) {
						$value = $sheet->getCellByColumnAndRow($column, $row)->getValue();
						$elements = $value->getRichTextElements();
						$cellData = "";
						foreach ($elements as $element) {
							if ($element instanceof Run) {
								if ($element->getFont()->getBold()) {
									$cellData .= '<b>';
								} elseif ($element->getFont()->getSubScript()) {
									$cellData .= '<sub>';
								} elseif ($element->getFont()->getSuperScript()) {
									$cellData .= '<sup>';
								} elseif ($element->getFont()->getItalic()) {
									$cellData .= '<em>';
								}
							}
							$cellText = $element->getText();
							$cellData .= htmlspecialchars($cellText);
							if ($element instanceof Run) {
								if ($element->getFont()->getBold()) {
									$cellData .= '</b>';
								} elseif ($element->getFont()->getSubScript()) {
									$cellData .= '</sub>';
								} elseif ($element->getFont()->getSuperScript()) {
									$cellData .= '</sup>';
								} elseif ($element->getFont()->getItalic()) {
									$cellData .= '</em>';
								}
							}
						}
						$a[$header[$column]] = $cellData;
					} else {
						$a[$header[$column]] = $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
					}
				} else {
					$key = $header[$column];
					$a[$key] = $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
				}
			}
			$array[$row] = $a;
		}

		return $array;
	}
}
