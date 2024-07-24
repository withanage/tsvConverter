<?php

namespace generic\tsvConverter\classes;

class JatsArticle
{

	/**
	 * @param  $xmlfile
	 * @param mixed $maxAuthors
	 * @param mixed $article
	 * @param $defaultUserGroupRef
	 * @param int $authorId
	 * @param string $articleLocale
	 * @return array
	 */
	public static function authors($xmlfile, mixed $maxAuthors, mixed $article, $defaultUserGroupRef, int $authorId, string $articleLocale): array
	{
		fwrite($xmlfile, "\t\t\t\t<authors xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://pkp.sfu.ca native.xsd\">\r\n");

		for ($i = 1; $i <= $maxAuthors; $i++) {

			if ($article['authorFirstname' . $i]) {

				fwrite($xmlfile, "\t\t\t\t\t<author include_in_browse=\"true\" user_group_ref=\"{$defaultUserGroupRef[$articleLocale]}\" seq=\"{$i}\" id=\"{$authorId}\">\r\n");

				fwrite($xmlfile, "\t\t\t\t\t\t<givenname locale=\"en_US\"><![CDATA[{$article['authorFirstname'.$i]} " . (!empty($article['authorMiddlename' . $i]) ? $article['authorMiddlename' . $i] : '') . "]]></givenname>\r\n");
				fwrite($xmlfile, Helpers::searchLocalisations('authorFirstname' . $i, $article, 6, 'givenname'));

				if (!empty($article['authorLastname' . $i])) {
					fwrite($xmlfile, "\t\t\t\t\t\t<familyname locale=\"{$articleLocale}\"><![CDATA[{$article['authorLastname'.$i]}]]></familyname>\r\n");
					fwrite($xmlfile, Helpers::searchLocalisations('authorLastname' . $i, $article, 6, 'familyname'));
				}

				if (!empty($article['authorAffiliation' . $i])) {
					fwrite($xmlfile, "\t\t\t\t\t\t<affiliation locale=\"{$articleLocale}\"><![CDATA[{$article['authorAffiliation'.$i]}]]></affiliation>\r\n");
					fwrite($xmlfile, Helpers::searchLocalisations('authorAffiliation' . $i, $article, 6, 'affiliation'));
				}

				if (!empty($article['country' . $i])) {
					fwrite($xmlfile, "\t\t\t\t\t\t<country><![CDATA[{$article['country'.$i]}]]></country>\r\n");
				}

				fwrite($xmlfile, "\t\t\t\t\t\t<email>" . (!empty($article['authorEmail' . $i]) ? $article['authorEmail' . $i] : '') . "</email>\r\n");

				if (!empty($article['orcid' . $i])) {
					fwrite($xmlfile, "\t\t\t\t\t\t<orcid><![CDATA[{$article['orcid'.$i]}]]></orcid>\r\n");
				}

				if (!empty($article['authorBio' . $i])) {
					fwrite($xmlfile, "\t\t\t\t\t\t<biography locale=\"{$articleLocale}\"><![CDATA[{$article['authorBio'.$i]}]]></biography>\r\n");
					fwrite($xmlfile, Helpers::searchLocalisations('authorBio' . $i, $article, 6, 'biography'));
				}

				fwrite($xmlfile, "\t\t\t\t\t</author>\r\n");


			}
			$authorId++;
		}
		# If no authors are given, use default author name
		$defaultAuthor['givenname'] = "Editorial Board";
		if (!$article['authorFirstname1']) {
			fwrite($xmlfile, "\t\t\t\t\t<author primary_contact=\"true\" user_group_ref=\"" . $defaultUserGroupRef[$articleLocale] . "\"  seq=\"0\" id=\"" . $authorId . "\">\r\n");
			fwrite($xmlfile, "\t\t\t\t\t\t<givenname><![CDATA[" . $defaultAuthor['givenname'] . "]]></givenname>\r\n");
			fwrite($xmlfile, "\t\t\t\t\t\t<email><![CDATA[]]></email>\r\n");
			fwrite($xmlfile, "\t\t\t\t\t</author>\r\n");
			$authorId++;
		}

		fwrite($xmlfile, "\t\t\t\t</authors>\r\n\r\n");


		return array($i, $article, $authorId);
	}


	public static function sections($xmlfile, $sections, mixed $article, string $defaultLocale): void
	{

		fwrite($xmlfile, "\t\t<sections>\r\n");

		foreach ($sections as $sectionAbbrev => $sectionTitle) {
			fwrite($xmlfile, "\t\t\t<section ref=\"" . htmlentities($sectionAbbrev, ENT_XML1) . "\" seq=\"" . htmlentities("0", ENT_XML1) . "\">\r\n");
			fwrite($xmlfile, "\t\t\t\t<abbrev locale=\"" . $defaultLocale . "\">" . htmlentities($sectionAbbrev, ENT_XML1) . "</abbrev>\r\n");
			fwrite($xmlfile, "\t\t\t\t<title locale=\"" . $defaultLocale . "\"><![CDATA[" . $sectionTitle . "]]></title>\r\n");
			fwrite($xmlfile, Helpers::searchLocalisations('sectionTitle', $article, 3));
			fwrite($xmlfile, "\t\t\t</section>\r\n");
		}

		fwrite($xmlfile, "\t\t</sections>\r\n\r\n");
	}

	public static function keywords(mixed $article, $xmlfile, string $articleLocale): mixed
	{
		if (!empty($article['keywords'])) {
			if (trim($article['keywords']) != "") {
				fwrite($xmlfile, "\t\t\t\t<keywords locale=\"" . $articleLocale . "\">\r\n");
				$keywords = explode(";", $article['keywords']);
				foreach ($keywords as $keyword) {
					fwrite($xmlfile, "\t\t\t\t\t<keyword><![CDATA[" . trim($keyword) . "]]></keyword>\r\n");
				}
				fwrite($xmlfile, "\t\t\t\t</keywords>\r\n");
			}
			fwrite($xmlfile, searchTaxonomyLocalisations('keywords', 'keyword', $article, 4));
		}
		return $article;
	}

	/**
	 * @param $galleys

	 * @return void
	 */
	public static function pages($galleys,  $xmlfile): void
	{
		if (isset($galleys)) {
			foreach ($galleys as $galley) {
				fwrite($xmlfile, $galley);
			}
		}
		if (!empty($article['pages'])) {
			fwrite($xmlfile, "\t\t\t\t<pages>" . $article['pages'] . "</pages>\r\n\r\n");
		}
	}

	/**
	 * @param mixed $article
	 * @param$xmlfile
	 * @param string $articleLocale
	 * @return mixed
	 */
	public static function disciplines(mixed $article,$xmlfile, string $articleLocale): mixed
	{
		if (!empty($article['disciplines'])) {
			if (trim($article['disciplines']) != "") {
				fwrite($xmlfile, "\t\t\t\t<disciplines locale=\"" . $articleLocale . "\">\r\n");
				$disciplines = explode(";", $article['disciplines']);
				foreach ($disciplines as $discipline) {
					fwrite($xmlfile, "\t\t\t\t\t<discipline><![CDATA[" . trim($discipline) . "]]></discipline>\r\n");
				}
				fwrite($xmlfile, "\t\t\t\t</disciplines>\r\n");
			}
			fwrite($xmlfile, searchTaxonomyLocalisations('disciplines', 'disciplin', $article, 4));
		}
		return $article;
	}

	/**
	 * @param mixed $article
	 * @param$xmlfile
	 * @param string $articleLocale
	 * @return mixed
	 */
	public static function subTitle(mixed $article,$xmlfile, string $articleLocale): mixed
	{
		if (!empty($article['subtitle'])) {
			fwrite($xmlfile, "\t\t\t\t<subtitle locale=\"" . $articleLocale . "\"><![CDATA[" . $article['subtitle'] . "]]></subtitle>\r\n");
		}
		fwrite($xmlfile, Helpers::searchLocalisations('subtitle', $article, 4));
		return $article;
	}

	/**
	 * @param mixed $article
	 * @param$xmlfile
	 * @param string $articleLocale
	 * @return mixed
	 */
	public static function abstractText(mixed $article,$xmlfile, string $articleLocale): mixed
	{
		if (!empty($article['abstract'])) {
			fwrite($xmlfile, "\t\t\t\t<abstract locale=\"" . $articleLocale . "\"><![CDATA[" . nl2br($article['abstract']) . "]]></abstract>\r\n\r\n");
		}
		fwrite($xmlfile, Helpers::searchLocalisations('abstract', $article, 4));
		return $article;
	}

	/**
	 * @param mixed $article
	 * @param$xmlfile
	 * @return mixed
	 */
	public static function articleLicenseUrl(mixed $article,$xmlfile): mixed
	{
		if (!empty($article['articleLicenseUrl'])) {
			fwrite($xmlfile, "\t\t\t\t<licenseUrl><![CDATA[" . $article['articleLicenseUrl'] . "]]></licenseUrl>\r\n");
		}
		return $article;
	}


	public static function articleCopyrightHolder(mixed $article,$xmlfile, string $articleLocale): mixed
	{
		if (!empty($article['articleCopyrightHolder'])) {
			fwrite($xmlfile, "\t\t\t\t<copyrightHolder locale=\"" . $articleLocale . "\"><![CDATA[" . $article['articleCopyrightHolder'] . "]]></copyrightHolder>\r\n");
		}
		return $article;
	}


	public static function articleCopyrightYear(mixed $article, $xmlfile): mixed
	{
		if (!empty($article['articleCopyrightYear'])) {
			fwrite($xmlfile, "\t\t\t\t<copyrightYear><![CDATA[" . $article['articleCopyrightYear'] . "]]></copyrightYear>\r\n");
		}
		return $article;
	}


	public static function doi(mixed $article,$xmlfile): mixed
	{
		if (!empty($article['doi'])) {
			fwrite($xmlfile, "\t\t\t\t<id type=\"doi\" advice=\"update\"><![CDATA[" . $article['doi'] . "]]></id>\r\n");
		}
		return $article;
	}

	public static function title($xmlfile, string $articleLocale, mixed $article): void
	{
		fwrite($xmlfile, "\t\t\t\t<title locale=\"" . $articleLocale . "\"><![CDATA[" . $article['title'] . "]]></title>\r\n");
		fwrite($xmlfile, Helpers::searchLocalisations('title', $article, 4));
	}

	public static function prefix(mixed $article,$xmlfile, string $articleLocale): mixed
	{
		if (!empty($article['prefix'])) {
			fwrite($xmlfile, "\t\t\t\t<prefix locale=\"" . $articleLocale . "\"><![CDATA[" . $article['prefix'] . "]]></prefix>\r\n");
		}
		fwrite($xmlfile, Helpers::searchLocalisations('prefix', $article, 4));
		return $article;
	}
}
