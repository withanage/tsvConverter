<?php

namespace generic\tsvConverter\classes;

class JatsIssue
{

	public static function issueDatePublished($xmlfile, $issueDatepublished): void
	{
		fwrite($xmlfile, "\t\t<date_published><![CDATA[" . $issueDatepublished . "]]></date_published>\r\n\r\n");
		fwrite($xmlfile, "\t\t<last_modified><![CDATA[" . $issueDatepublished . "]]></last_modified>\r\n\r\n");
	}

	public static function issue($xmlfile, mixed $article): mixed
	{
		fwrite($xmlfile, "\t\t<issue_identification>\r\n");

		if (!empty($article['issueVolume'])) fwrite($xmlfile, "\t\t\t<volume><![CDATA[" . $article['issueVolume'] . "]]></volume>\r\n");
		if (!empty($article['issueNumber'])) fwrite($xmlfile, "\t\t\t<number><![CDATA[" . $article['issueNumber'] . "]]></number>\r\n");
		fwrite($xmlfile, "\t\t\t<year><![CDATA[" . $article['issueYear'] . "]]></year>\r\n");

		if (!empty($article['issueTitle'])) {
			fwrite($xmlfile, "\t\t\t<title><![CDATA[" . $article['issueTitle'] . "]]></title>\r\n");
		}
		# Add alternative localisations for the issue title
		fwrite($xmlfile, Helpers::searchLocalisations('issueTitle', $article, 3));

		fwrite($xmlfile, "\t\t</issue_identification>\r\n\r\n");
		return $article;
	}


	public static function issueDescription(mixed $article, $xmlfile, string $defaultLocale): mixed
	{
		if (!empty($article['issueDescription'])) fwrite($xmlfile, "\t\t<description locale=\"" . $defaultLocale . "\"><![CDATA[" . $article['issueDescription'] . "]]></description>\r\n");
		return $article;
	}
}
