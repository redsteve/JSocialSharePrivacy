<?php
/**
 * Social Share Privacy PlugIn for Joomla! 1.7 or higher
 * @link      https://github.com/redsteve/JSocialSharePrivacy
 * @copyright Copyright (C) 2011 Stephan Roth. All rights reserved.
 * @license   MIT License; see MIT-LICENSE file
 */

defined('_JEXEC') or die('Restricted access!');

jimport('joomla.plugin.plugin');

define('SOCIAL_SHARE_PRIVACY_RELATIVE_URL', "plugins/content/SocialSharePrivacy/");
define('SOCIAL_SHARE_PRIVACY_BASE_URL', JURI::base() . SOCIAL_SHARE_PRIVACY_RELATIVE_URL);

final class plgContentSocialSharePrivacy extends JPlugin {

	const facebookServiceName = "facebook";
	const twitterServiceName = "twitter";
	const googleServiceName = "gplus";
	const socialSharePrivacyDivElement = "\n<div id=\"socialshareprivacy\"></div>\n";
	const heisePluginFolderName = "socialshareprivacy";
	const defaultDummyImagePath = "socialshareprivacy/images/";
	const defaultCssPathAndFilename = "socialshareprivacy/socialshareprivacy.css";
	const socialSharePrivacyJScript = "jquery.socialshareprivacy.js";
	const socialSharePrivacyMinimizedJScript = "jquery.socialshareprivacy.min.js";

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onContentPrepare($context, &$article, &$params, $limitstart) {
		$application = &JFactory::getApplication();
		
		if ($this->isAdministrationArea($application))
			return;
		
		$currentOption = JRequest::getCmd("option");
		if (($currentOption != "com_content")
			OR !isset($article)
			OR empty($article->id)
			OR !isset($this->params)) {
            return;            
        }
		
		$document = &JFactory::getDocument();
		if (! $this->isHtmlDocument($document))
			return;

		if (! $this->isArticle())
			return;

		$this->addCustomTagsToHeader($document);
		$this->insertSocialshareprivacyDivElementIntoArticle($article);
		
		return true;
    }

	private function insertSocialshareprivacyDivElementIntoArticle($article) {
		$position = $this->params->get('position');
		
		switch ($position) {
			case 1 :
				$article->text = self::socialSharePrivacyDivElement . $article->text;
				break;
				
			case 2 :
				$article->text = $article->text . self::socialSharePrivacyDivElement;
				break;
		}
	}
	
    private function isAdministrationArea(&$application) {
		return ($application->isAdmin());
	}

	private function isHtmlDocument(&$document) {
		$documentType = $document->getType();
		return (strcmp("html", $documentType) == 0);
	}

	private function isArticle() {
		$view = JRequest::getString('view', '');
		return ($view == 'article');
	}

	private function isParameterJQueryUrlNotEmpty()	{
		$jQueryUrl = $this->params->get('jqueryurl');
		return (! (empty($jQueryUrl)));
	}

	private function isParameterFacebookDummyImageNotEmpty() {
		$facebookDummyImage = $this->params->get('facebookDummyImage');
		return (! (empty($facebookDummyImage)));
	}

	private function isParameterTwitterDummyImageNotEmpty() {
		$twitterDummyImage = $this->params->get('twitterDummyImage');
		return (! (empty($twitterDummyImage)));
	}

	private function isParameterGoogleDummyImageNotEmpty() {
		$googleDummyImage = $this->params->get('googleDummyImage');
		return (! (empty($googleDummyImage)));
	}

	private function isParameterFacebookReferrerTrackNotEmpty()	{
		$facebookReferrerTrack = $this->params->get('facebookReferrerTrack');
		return (! (empty($facebookReferrerTrack)));
	}

	private function isParameterTwitterReferrerTrackNotEmpty() {
		$twitterReferrerTrack = $this->params->get('twitterReferrerTrack');
		return (! (empty($twitterReferrerTrack)));
	}

	private function isParameterGoogleReferrerTrackNotEmpty() {
		$googleReferrerTrack = $this->params->get('googleReferrerTrack');
		return (! (empty($googleReferrerTrack)));
	}

	private function isParameterCssPathNotEmpty() {
		$cssPath = $this->params->get('cssPath');
		return (! (empty($cssPath)));
	}

	private function isParameterCookieDomainNotEmpty() {
		$cookieDomain = $this->params->get('cookieDomain');
		return (! (empty($cookieDomain)));
	}

    private function addCustomTagsToHeader(&$document) {
		if ($this->isParameterJQueryUrlNotEmpty()) {
			$this->addJQueryUrlToHeader($document);
		}
		$this->addHeiseSocialSharePrivacyJScriptToHeader($document);
		$this->addOptionsJScriptToHeader($document);
	}

	private function addJQueryUrlToHeader(&$document) {
		$jQueryUrl = $this->params->get('jqueryurl');
		$document->addScript($jQueryUrl);
	}

	private function addHeiseSocialSharePrivacyJScriptToHeader(&$document) {
		$heisePluginUrl = $this->getHeisePluginUrl();
		$useCompressedScript = $this->params->get('compressed');
		
		if ($useCompressedScript == 1) {
			$document->addScript($heisePluginUrl . self::socialSharePrivacyMinimizedJScript);
		} else {
			$document->addScript($heisePluginUrl . self::socialSharePrivacyJScript);
		}
	}

	private function addOptionsJScriptToHeader(&$document) {
		$heisePluginConfig = $this->getHeisePluginConfig();
		$javascript = $this->getHeisePluginConfigJavaScript($heisePluginConfig);
		$document->addCustomTag($javascript);
	}

	private function getHeisePluginUrl() {
		$heisePluginUrl = SOCIAL_SHARE_PRIVACY_BASE_URL;
		$heisePluginUrl .= self::heisePluginFolderName;
		$heisePluginUrl .= "/";
		
		return $heisePluginUrl;
	}

	private function getHeisePluginConfig() {
		$config = "\n    services : {";
		
		$this->appendFacebookConfigSection($config);
		$this->appendTwitterConfigSection($config);
		$this->appendGooglePlusConfigSection($config);
		
		$config .= "\n    },";
		
		$this->appendCommonConfigSection($config);
		
		return $config;
	}

	private function appendFacebookConfigSection(&$config) {
		$this->openSocialServiceConfigSection($config, self::facebookServiceName);
		
		$displayFacebook = $this->params->get('displayFacebook');
		$this->appendEnableServiceConfigFragment($config, $displayFacebook);
		
		if ($displayFacebook) {
			$facebookDummyImage = "";
			if ($this->isParameterFacebookDummyImageNotEmpty()) {
				$facebookDummyImage = $this->params->get('facebookDummyImage');
			} else {
				$facebookDummyImage = $this->getFacebookDefaultDummyImagePathAndFilename();
			}
			$this->appendDummyImageOptionFragment($config, $facebookDummyImage);
			
			$facebookPermaOption = $this->params->get('facebookPermaOption');
			$this->appendEnablePermaOptionFragment($config, $facebookPermaOption);
			
			if ($this->isParameterFacebookReferrerTrackNotEmpty()) {
				$facebookReferrerTrack = $this->params->get('facebookReferrerTrack');
				$this->appendReferrerTrackOptionFragment($config, $facebookReferrerTrack);
			}
		}
		
		$this->closeSocialServiceConfigSection($config);
	}

	private function appendTwitterConfigSection(&$config) {
		$this->openSocialServiceConfigSection($config, self::twitterServiceName);
		
		$displayTwitter = $this->params->get('displayTwitter');
		$this->appendEnableServiceConfigFragment($config, $displayTwitter);
		
		if ($displayTwitter) {
			$twitterDummyImage = "";
			if ($this->isParameterTwitterDummyImageNotEmpty()) {
				$twitterDummyImage = $this->params->get('twitterDummyImage');
			} else {
				$twitterDummyImage = $this->getTwitterDefaultDummyImagePathAndFilename();
			}
			$this->appendDummyImageOptionFragment($config, $twitterDummyImage);
			
			$twitterPermaOption = $this->params->get('twitterPermaOption');
			$this->appendEnablePermaOptionFragment($config, $twitterPermaOption);
			
			if ($this->isParameterTwitterReferrerTrackNotEmpty()) {
				$twitterReferrerTrack = $this->params->get('twitterReferrerTrack');
				$this->appendReferrerTrackOptionFragment($config, $twitterReferrerTrack);
			}
		}
		
		$this->closeSocialServiceConfigSection($config);
	}

	private function appendGooglePlusConfigSection(&$config) {
		$this->openSocialServiceConfigSection($config, self::googleServiceName);
		
		$displayGooglePlus = $this->params->get('displayGooglePlus');
		$this->appendEnableServiceConfigFragment($config, $displayGooglePlus);
		
		if ($displayGooglePlus) {
			$googleDummyImage = "";
			if ($this->isParameterGoogleDummyImageNotEmpty()) {
				$googleDummyImage = $this->params->get('googleDummyImage');
			} else {
				$googleDummyImage = $this->getGoogleDefaultDummyImagePathAndFilename();
			}
			$this->appendDummyImageOptionFragment($config, $googleDummyImage);
			
			$googlePermaOption = $this->params->get('googlePermaOption');
			$this->appendEnablePermaOptionFragment($config, $googlePermaOption);
			
			if ($this->isParameterGoogleReferrerTrackNotEmpty()) {
				$googleReferrerTrack = $this->params->get('googleReferrerTrack');
				$this->appendReferrerTrackOptionFragment($config, $googleReferrerTrack);
			}
		}
		
		$this->closeSocialServiceConfigSection($config);
	}

	private function openSocialServiceConfigSection(&$config, $serviceName) {
		$config .= "\n      ";
		$config .= $serviceName;
		$config .= " : {";
	}
	
	private function closeSocialServiceConfigSection(&$config) {
		$config .= "\n      },";
	}
	
	private function appendEnableServiceConfigFragment(&$config, $enableFlag) {
		$config .= "\n        'status' : ";
		$this->appendBooleanFlag(&$config, $enableFlag);
	}

	private function appendEnablePermaOptionFragment(&$config, $enableFlag) {
		$config .= "\n        'perma_option' : ";
		$this->appendBooleanFlag(&$config, $enableFlag);
	}

	private function appendDummyImageOptionFragment(&$config, $dummyImagePathAndFilename) {
		$config .= "\n        'dummy_img' : '";
		$config .= $dummyImagePathAndFilename;
		$config .= "',";
	}

	private function getFacebookDefaultDummyImagePathAndFilename() {
		return $this->getDefaultDummyImagePathAndFilename(self::facebookServiceName);
	}

	private function getTwitterDefaultDummyImagePathAndFilename() {
		return $this->getDefaultDummyImagePathAndFilename(self::twitterServiceName);
	}

	private function getGoogleDefaultDummyImagePathAndFilename() {
		return $this->getDefaultDummyImagePathAndFilename(self::googleServiceName);
	}

	private function getDefaultDummyImagePathAndFilename($serviceName) {
		$dummyImagePathAndFilename  = SOCIAL_SHARE_PRIVACY_BASE_URL;
		$dummyImagePathAndFilename .= self::heisePluginFolderName;
		$dummyImagePathAndFilename .= "/";
		$dummyImagePathAndFilename .= self::defaultDummyImagePath;
		$dummyImagePathAndFilename .= "dummy_";
		$dummyImagePathAndFilename .= $serviceName;
		$dummyImagePathAndFilename .= ".png";
		
		return $dummyImagePathAndFilename;
	}

	private function appendReferrerTrackOptionFragment(&$config, $referrerTrack) {
		$config .= "\n        'referrer_track' : '";
		$config .= $referrerTrack;
		$config .= "',";
	}

	private function appendBooleanFlag(&$config, $enableFlag) {
		if ($enableFlag) {
			$config .= "'on',";
		} else {
			$config .= "'off',";
		}
	}

	private function appendCommonConfigSection(&$config) {
		$cssPathAndFilename = "";
		if ($this->isParameterCssPathNotEmpty()) {
			$cssPathAndFilename = $this->params->get('cssPath');
		} else {
			$cssPathAndFilename = $this->getDefaultCssPathAndFilename();
		}
		$this->appendCssPathOptionFragment($config, $cssPathAndFilename);
		
		if ($this->isParameterCookieDomainNotEmpty()) {
			$cookieDomain = $this->params->get('cookieDomain');
			$this->appendCookieDomainOptionFragment($config, $cookieDomain);
		}
	}

	private function getDefaultCssPathAndFilename()
	{
		$defaultCssPathAndFilename  = SOCIAL_SHARE_PRIVACY_BASE_URL;
		$defaultCssPathAndFilename .= self::heisePluginFolderName;
		$defaultCssPathAndFilename .= "/";
		$defaultCssPathAndFilename .= self::defaultCssPathAndFilename;
		
		return $defaultCssPathAndFilename ;
	}

	private function appendCssPathOptionFragment(&$config, $cssPathAndFilename) {

		$config .= "\n      'css_path' : '";
		$config .= $cssPathAndFilename;
		$config .= "',";
	}

	private function appendCookieDomainOptionFragment(&$config, $cookieDomain) {

		$config .= "\n      'cookie_domain' : '";
		$config .= $cookieDomain;
		$config .= "',";
	}

	private function getHeisePluginConfigJavaScript($heisePluginConfig) {
		$javascript  = "  <script type=\"text/javascript\">\n";
		$javascript .= "    jQuery(document).ready(function($) {\n";
		$javascript .= "      if ($('#socialshareprivacy').length > 0) {\n";
		$javascript .= "        $('#socialshareprivacy').socialSharePrivacy({" . $heisePluginConfig . "})\n";
		$javascript .= "      }});\n";
		$javascript .= "  </script>\n";
		
		
		return $javascript;
	}
}
