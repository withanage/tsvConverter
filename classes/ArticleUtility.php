<?php

namespace generic\tsvConverter\classes;

class ArticleUtility
{
	public static function countMaxAuthors($sheet)
	{
		$highestcolumn = $sheet->getHighestColumn();
		$headerRow = $sheet->rangeToArray('A1:' . $highestcolumn . "1");
		$header = $headerRow[0];
		$authorFirstnameValues = array();
		foreach ($header as $headerValue) {
			if ($headerValue && strpos($headerValue, "authorFirstname") !== false) {
				$authorFirstnameValues[] = (int)trim(str_replace("authorFirstname", "", $headerValue));
			}
		}
		return max($authorFirstnameValues);
	}

	public static function countMaxFiles($sheet)
	{
		$highestcolumn = $sheet->getHighestColumn();
		$headerRow = $sheet->rangeToArray('A1:' . $highestcolumn . "1");
		$header = $headerRow[0];
		$fileValues = array();
		foreach ($header as $headerValue) {
			if ($headerValue && strpos($headerValue, "fileLabel") !== false) {
				$fileValues[] = (int)trim(str_replace("fileLabel", "", $headerValue));
			}
		}
		return max($fileValues);
	}

	public static function validateArticles($articles, $filesFolder)
	{
		$errors = "";
		$articleRow = 0;

		foreach ($articles as $article) {
			$articleRow++;

			if (empty($article['issueYear'])) {
				$errors .= date('H:i:s') . " ERROR: Issue year missing for article " . $articleRow . PHP_EOL;
			}

			if (empty($article['issueDatepublished'])) {
				$errors .= date('H:i:s') . " ERROR: Issue publication date missing for article " . $articleRow . PHP_EOL;
			}

			if (empty($article['title'])) {
				$errors .= date('H:i:s') . " ERROR: Article title missing for the given default locale for article " . $articleRow . PHP_EOL;
			}

			if (empty($article['sectionTitle'])) {
				$errors .= date('H:i:s') . " ERROR: Section title missing for the given default locale for article " . $articleRow . PHP_EOL;
			}

			if (empty($article['sectionAbbrev'])) {
				$errors .= date('H:i:s') . " ERROR: Section abbreviation missing for the given default locale for article " . $articleRow . PHP_EOL;
			}

			for ($i = 1; $i <= 200; $i++) {
				if (isset($article['file' . $i]) && $article['file' . $i] && !preg_match("@^https?://@", $article['file' . $i])) {
					$fileCheck = $filesFolder . $article['file' . $i];

					if (!file_exists($fileCheck)) {
						$errors .= date('H:i:s') . " ERROR: File " . $i . " missing " . $fileCheck . PHP_EOL;
					}

					$fileLabelColumn = 'fileLabel' . $i;
					if (empty($article[$fileLabelColumn])) {
						$errors .= date('H:i:s') . " ERROR: FileLabel " . $i . " missing for article " . $articleRow . PHP_EOL;
					}

					$fileLocaleColumns = 'fileLocale' . $i;
					if (empty($article[$fileLocaleColumns])) {
						$errors .= date('H:i:s') . " ERROR: FileLocale " . $i . " missing for article " . $articleRow . PHP_EOL;
					}
				} else {
					break;
				}
			}
		}

		return $errors;
	}
}
