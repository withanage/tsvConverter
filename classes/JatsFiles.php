<?php

namespace generic\tsvConverter\classes;

use finfo;

class JatsFiles
{

	/**
	 * @param mixed $article
	 * @param int $i
	 * @param string $filesFolder
	 * @param bool|string $xmlfile
	 * @param int $submission_file_id
	 * @param int $file_id
	 * @param string $uploader
	 * @param string $articleLocale
	 * @param $locales
	 * @param string $fileLocale
	 * @param int $fileSeq
	 * @return array
	 */
	public static function submissionFile(mixed $article, int $i, string $filesFolder, $xmlfile, int $submission_file_id, int $file_id, string $uploader, string $articleLocale, $locales, string $fileLocale, int $fileSeq): array
	{
		if (!preg_match("@^https?://@", $article['file' . $i]) && $article['file' . $i] != "") {

			$file = $filesFolder . $article['file' . $i];
			$fileSize = filesize($file);
			if (function_exists('mime_content_type')) {
				$fileType = mime_content_type($file);
			} elseif (function_exists('finfo_open')) {
				$fileinfo = new finfo();
				$fileType = $fileinfo->file($file, FILEINFO_MIME_TYPE);
			} else {
				echo date('H:i:s'), " ERROR: You need to enable fileinfo or mime_magic extension.", EOL;
			}
			$fileExtension = pathinfo($file)['extension'];

			$fileContents = file_get_contents($file);

			fwrite($xmlfile, "\t\t\t<submission_file xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" stage=\"proof\"  id=\"" . $submission_file_id . "\" file_id=\"" . $file_id . "\" uploader=\"" . $uploader . "\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\" genre=\"" . trim($article['fileGenre' . $i]) . "\">\r\n");
			fwrite($xmlfile, "\t\t\t\t<name locale=\"" . $articleLocale . "\">" . trim(htmlentities($article['file' . $i], ENT_XML1)) . "</name>\r\n");
			if (empty($article['fileGenre' . $i])) $article['fileGenre' . $i] = "Article Text";

			fwrite($xmlfile, "\t\t\t\t<file id=\"" . $file_id . "\" filesize=\"" . $fileSize . "\" extension=\"" . $fileExtension . "\">\r\n");

			fwrite($xmlfile, "\t\t\t\t<embed encoding=\"base64\">");
			fwrite($xmlfile, base64_encode($fileContents));
			fwrite($xmlfile, "\t\t\t\t</embed>\r\n");

			fwrite($xmlfile, "\t\t\t\t</file>\r\n");
			fwrite($xmlfile, "\t\t\t</submission_file>\r\n\r\n");

			# save galley data
			$galleys[$submission_file_id] = "\t\t\t\t<article_galley xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" locale=\"" . $locales . "\" approved=\"false\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n";
			$galleys[$submission_file_id] .= "\t\t\t\t\t<name locale=\"" . $fileLocale . "\">" . $article['fileLabel' . $i] . "</name>\r\n";

			$galleys[$submission_file_id] .= Helpers::searchLocalisations('fileLabel' . $i, $article, 5, 'name');
			$galleys[$submission_file_id] .= "\t\t\t\t\t<seq>" . $fileSeq . "</seq>\r\n";
			$galleys[$submission_file_id] .= "\t\t\t\t\t<submission_file_ref id=\"" . $submission_file_id . "\"/>\r\n";
			$galleys[$submission_file_id] .= "\t\t\t\t</article_galley>\r\n\r\n";

			$submission_file_id++;
		}
		return array( $galleys, $submission_file_id);
	}

	/**
	 * @param mixed $article
	 * @param int $i
	 * @param $locales
	 * @param mixed $galleys
	 * @param mixed $submission_file_id
	 * @param string $fileLocale
	 * @param int $fileSeq
	 * @return mixed
	 */
	public static function externalFile(mixed $article, int $i, $locales, mixed $galleys, mixed $submission_file_id, string $fileLocale, int $fileSeq): mixed
	{
		if (preg_match("@^https?://@", $article['file' . $i]) && $article['file' . $i] != "") {
			# save remote galley data
			$galleys[$submission_file_id] = "\t\t\t\t<article_galley xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" locale=\"" . $locales . "\" approved=\"false\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n";
			$galleys[$submission_file_id] .= "\t\t\t\t\t<name locale=\"" . $fileLocale . "\">" . $article['fileLabel' . $i] . "</name>\r\n";
			$galleys[$submission_file_id] .= Helpers::searchLocalisations('fileLabel' . $i, $article, 5, 'name');
			$galleys[$submission_file_id] .= "\t\t\t\t\t<seq>" . $fileSeq . "</seq>\r\n";
			$galleys[$submission_file_id] .= "\t\t\t\t\t<remote src=\"" . trim(htmlentities($article['file' . $i], ENT_XML1)) . "\" />\r\n";
			$galleys[$submission_file_id] .= "\t\t\t\t</article_galley>\r\n\r\n";
		}
		return $galleys;
	}

	/**
	 * @param mixed $article
	 * @param int $i
	 * @param string $articleLocale
	 * @param $locales
	 * @return array
	 */
	public static function fileLocale(mixed $article, int $i, string $articleLocale, $locales): string
	{
		if (empty($article['fileLocale' . $i])) {
			$fileLocale = $articleLocale;
		} else {
			$fileLocale = $locales;
		}
		return $fileLocale;
	}
}
