<?php

require 'vendor/autoload.php';
require_once __DIR__ . '/classes/ArticleUtility.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/classes/JatsArticle.php';
require_once __DIR__ . '/classes/JatsIssue.php';
require_once __DIR__ . '/classes/JatsFiles.php';

use generic\tsvConverter\classes\ArticleUtility;
use generic\tsvConverter\classes\Helpers;
use generic\tsvConverter\classes\JatsArticle;
use generic\tsvConverter\classes\JatsFiles;
use generic\tsvConverter\classes\JatsIssue;
use PhpOffice\PhpSpreadsheet\IOFactory;


# Usage:
# php convert.php sheetFilename filesFolderName

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/Berlin');
define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$fileName = '';
$files = "files";
$onlyValidate = 0;
$locales = array('en' => 'en_US', 'fi' => 'fi_FI', 'sv' => 'sv_SE', 'de' => 'de_DE', 'ge' => 'de_DE', 'ru' => 'ru_RU', 'fr' => 'fr_FR', 'no' => 'nb_NO', 'da' => 'da_DK', 'es' => 'es_ES',);
$defaultLocale = 'en_US';
$uploader = "admin";
$currentIssueDatepublished = null;
$currentYear = null;
$submission_file_id = 1;
$authorId = 1;
$submissionId = 1;
$file_id = 1;

$defaultUserGroupRef = array('en_US' => 'Author', 'de_DE' => 'Author', 'sv_SE' => 'F&#xF6;rfattare');

// Get arguments from command line
# $argv[1] fileName
if (isset($argv[1])) {
	$fileName = $argv[1];
}
# $argv[2] files folder
if (isset($argv[2])) {
	$files = $argv[2];
}
# $argv[3] validate parameter
if (isset($argv[3]) && $argv[3] == '-v') {
	$onlyValidate = 1;
}

#							'de_DE' => 'Autor/in',
$filesFolder = dirname(__FILE__) . "/" . $files . "/";


/*
 * Check that a file and a folder exists
 * ------------------------------------
 */
if (!file_exists($fileName)) {
	echo date('H:i:s') . " ERROR: given file does not exist" . EOL;
	die();
}

if (!file_exists($filesFolder)) {
	echo date('H:i:s') . " ERROR: given folder does not exist" . EOL;
	die();
}

/*
 * Load Excel data to an array
 * ------------------------------------
 */
echo date('H:i:s'), " Creating a new PHPExcel object", EOL;

$objReader = IOFactory::createReaderForFile($fileName);
$objReader->setReadDataOnly(false);
$objPhpSpreadsheet = $objReader->load($fileName);
$sheet = $objPhpSpreadsheet->setActiveSheetIndex(0);
$xmlfile = '';

echo date('H:i:s'), " Creating an array", EOL;

$articles = Helpers::createArray($sheet);

$maxAuthors = ArticleUtility::countMaxAuthors($sheet);

$maxFiles = ArticleUtility::countMaxFiles($sheet);

$errors = ArticleUtility::validateArticles($articles, $filesFolder);

/*
 * Data validation
 * -----------
 */


if ($errors != "") {
	echo $errors, EOL;
	die();
}

# If only validation is selected, exit
if ($onlyValidate == 1) {
	echo date('H:i:s'), " Validation complete ", EOL;
	die();
}


echo date('H:i:s'), " Preparing data for output", EOL;

# Save section data
foreach ($articles as $article) {
	$sections[$article['issueDatepublished']][$article['sectionAbbrev']] = $article['sectionTitle'];
}

/*
 * Create XML
 * --------------------
 */

function compareIssueDate($a, $b)
{
	// Convert the issueDatepublished fields to timestamps for comparison
	$dateA = strtotime($a['issueDatepublished']);
	$dateB = strtotime($b['issueDatepublished']);

	return $dateA - $dateB;
}

// Sort the articles array using usort and the comparison function
usort($articles, 'compareIssueDate');

echo date('H:i:s'), " Starting XML output", EOL;


/**
 * @param bool|string $xmlfile
 * @param mixed $article
 * @return mixed
 */


/**
 * @param mixed $article
 * @param string $articleLocale
 * @param int $authorId
 * @param int $submissionId
 * @return void
 */
function publicationBeginTag(mixed $article, $xmlfile, string $articleLocale, int $authorId, int $submissionId): void
{
	if (array_key_exists('articleSeq', $article)) {
		fwrite($xmlfile, "\t\t\t<publication xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" locale=\"" . $articleLocale . "\" version=\"1\" status=\"3\" primary_contact_id=\"" . $authorId . "\" url_path=\"\" seq=\"" . $article['articleSeq'] . "\" date_published=\"" . $article['issueDatepublished'] . "\" section_ref=\"" . htmlentities($article['sectionAbbrev'], ENT_XML1) . "\" access_status=\"0\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n\r\n");
	} else {
		fwrite($xmlfile, "\t\t\t<publication xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" locale=\"" . $articleLocale . "\" version=\"1\" status=\"3\" primary_contact_id=\"" . $authorId . "\" url_path=\"\" date_published=\"" . $article['issueDatepublished'] . "\" section_ref=\"" . htmlentities($article['sectionAbbrev'], ENT_XML1) . "\" access_status=\"0\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n\r\n");
	}
	fwrite($xmlfile, "\t\t\t\t<id type=\"internal\" advice=\"ignore\">" . $submissionId . "</id>\r\n\r\n");
}

/**
 * @param bool|string $xmlfile
 * @return void
 */
function publicationEndTag($xmlfile): void
{
	fwrite($xmlfile, "\t\t\t</publication>\r\n\r\n");
}

/**
 * @param mixed $article
 * @param $xmlfile
 * @param string $articleLocale
 * @param mixed $maxAuthors
 * @param array $defaultUserGroupRef
 * @param int $authorId
 * @param mixed $galleys
 * @return void
 */
function publicationBody(mixed $article, $xmlfile, string $articleLocale, mixed $maxAuthors, array $defaultUserGroupRef, int $authorId, mixed $galleys): void
{
	JatsArticle::doi($article, $xmlfile);
	JatsArticle::title($xmlfile, $articleLocale, $article);
	JatsArticle::prefix($article, $xmlfile, $articleLocale);
	JatsArticle::subTitle($article, $xmlfile, $articleLocale);
	JatsArticle::abstractText($article, $xmlfile, $articleLocale);
	JatsArticle::articleLicenseUrl($article, $xmlfile);
	JatsArticle::articleCopyrightHolder($article, $xmlfile, $articleLocale);
	JatsArticle::articleCopyrightYear($article, $xmlfile);
	JatsArticle::keywords($article, $xmlfile, $articleLocale);
	JatsArticle::disciplines($article, $xmlfile, $articleLocale);
	JatsArticle::authors($xmlfile, $maxAuthors, $article, $defaultUserGroupRef, $authorId, $articleLocale);
	JatsArticle::pages($galleys, $xmlfile);
}

/**
 * @param mixed $article
 * @param bool|string $xmlfile
 * @param string $defaultLocale
 * @param $sections1
 * @param string $newYear
 * @return array
 */
function issue(mixed $article, $xmlfile, string $defaultLocale, $sections1, string $newYear): array
{
	$article = JatsIssue::issueDescription($article, $xmlfile, $defaultLocale);
	JatsIssue::issue($xmlfile, $article);
	JatsIssue::issueDatePublished($xmlfile, $article['issueDatepublished']);
	JatsArticle::sections($xmlfile, $sections1, $article, $defaultLocale);

	# Issue galleys needed even if empty
	fwrite($xmlfile, "\t\t<issue_galleys xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\"/>\r\n\r\n");

	# Start articles output
	fwrite($xmlfile, "\t\t<articles xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n\r\n");

	$currentIssueDatepublished = $article['issueDatepublished'];
	$currentYear = $newYear;
	return array($article, $currentIssueDatepublished, $currentYear);
}

foreach ($articles as $key => $article) {

	# Issue :: if issueDatepublished has changed, start a new issue
	if ($currentIssueDatepublished != $article['issueDatepublished']) {


		# close old issue if one exists
		if ($currentIssueDatepublished != null) {
			fwrite($xmlfile, "\t\t</articles>\r\n");
			fwrite($xmlfile, "\t</issue>\r\n\r\n");
		}


		$newYear = date('Y', strtotime($article['issueDatepublished']));
		# Start a new XML file if year changes
		if ($newYear != $currentYear) {

			if ($currentYear != null) {
				echo date('H:i:s'), " Closing XML file", EOL;
				fwrite($xmlfile, "</issues>\r\n\r\n");
			}

			echo date('H:i:s'), " Creating a new XML file ", $newYear, ".xml", EOL;

			$xmlfile = fopen($newYear . '.xml', 'w');
			fwrite($xmlfile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n");
			fwrite($xmlfile, "<issues xmlns=\"http://pkp.sfu.ca\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n");
		}

		fwrite($xmlfile, "\t<issue xmlns=\"http://pkp.sfu.ca\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" published=\"1\" current=\"0\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n\r\n");

		echo date('H:i:s'), " Adding issue with publishing date ", $article['issueDatepublished'], EOL;

		list($article, $currentIssueDatepublished, $currentYear) = issue($article, $xmlfile, $defaultLocale, $sections[$article['issueDatepublished']], $newYear);


	}


	# Article
	echo date('H:i:s'), " Adding article: ", $article['title'], EOL;


	$articleLocale = $defaultLocale;
	$currentLanguage = $article['language'];
	if (!empty($currentLanguage)) {

		$articleLocale = trim($currentLanguage);
	} else {
		$articleLocale = $defaultLocale;
	}


	fwrite($xmlfile, "\t\t<article xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" date_submitted=\"" . $article['issueDatepublished'] . "\" status=\"3\" submission_progress=\"0\" current_publication_id=\"" . $submissionId . "\" stage=\"production\">\r\n\r\n");
	fwrite($xmlfile, "\t\t\t<id type=\"internal\" advice=\"ignore\">" . $submissionId . "</id>\r\n\r\n");

	# Submission files
	unset($galleys);
	$fileSeq = 0;

	for ($i = 1; $i <= $maxFiles; $i++) {

		$fileLocale = JatsFiles::fileLocale($article, $i, $articleLocale, $locales[trim($article['fileLocale' . $i])]);
		list($galleys, $submission_file_id) = JatsFiles::submissionFile($article, $i, $filesFolder, $xmlfile, $submission_file_id, $file_id, $uploader, $articleLocale, $locales[trim($article['fileLocale' . $i])], $fileLocale, $fileSeq);
		$galleys = JatsFiles::externalFile($article, $i, $locales[trim($article['fileLocale' . $i])], $galleys, $submission_file_id, $fileLocale, $fileSeq);
		$fileSeq++;
		$file_id++;
	}

	# Publication
	publicationBeginTag($article, $xmlfile, $articleLocale, $authorId, $submissionId);
	publicationBody($article, $xmlfile, $articleLocale, $maxAuthors, $defaultUserGroupRef, $authorId, $galleys);
	publicationEndTag($xmlfile);

	fwrite($xmlfile, "\t\t</article>\r\n\r\n");
	$submissionId++;
}

# After exiting the loop close the last XML file
echo date('H:i:s'), " Closing XML file", EOL;
fwrite($xmlfile, "\t\t</articles>\r\n");
fwrite($xmlfile, "\t</issue>\r\n\r\n");
fwrite($xmlfile, "</issues>\r\n\r\n");


echo date('H:i:s'), " Conversion finished", EOL;

