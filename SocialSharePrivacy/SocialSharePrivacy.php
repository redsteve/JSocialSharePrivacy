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

	const divElementToSubstitute = "\n<div id=\"socialshareprivacy\"></div>\n";
	const heisePluginFolderName = "socialshareprivacy";
	const defaultCssPathAndFilename = "socialshareprivacy\socialshareprivacy.css";
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
			
		$document = &JFactory::getDocument();
		if (! $this->isHtmlDocument($document))
			return;
			
		if (! $this->isArticle())
			return;
			
		$this->addCustomTagsToHeader($document);
		
		$position = $this->params->get('position');
		
		switch ($position) {
			case 1 :
				$article->text = self::divElementToSubstitute . $article->text;
				break;
				
			case 2 :
				$article->text = $article->text . self::divElementToSubstitute;
				break;
		}
		
		return true;
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

	private function isParameterFacebookReferrerTrackNotEmpty()	{
		$facebookReferrerTrack = $this->params->get('facebookReferrerTrack');
		return (! (empty($facebookReferrerTrack)));
	}
	
	private function isParameterTwitterReferrerTrackNotEmpty()	{
		$twitterReferrerTrack = $this->params->get('twitterReferrerTrack');
		return (! (empty($twitterReferrerTrack)));
	}
	
	private function isParameterGoogleReferrerTrackNotEmpty()	{
		$googleReferrerTrack = $this->params->get('googleReferrerTrack');
		return (! (empty($googleReferrerTrack)));
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
		/** $document->addScriptDeclaration($javascript); */
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
		
		$this->appendCommonConfig($config);
		
		return $config;
	}

	private function appendFacebookConfigSection(&$config) {
		$this->openSocialServiceConfigSection($config, "facebook");
		
		$displayFacebook = $this->params->get('displayFacebook');
		$this->appendEnableServiceConfigFragment($config, $displayFacebook);
		
		if ($displayFacebook) {
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
		$this->openSocialServiceConfigSection($config, "twitter");
		
		$displayTwitter = $this->params->get('displayTwitter');
		$this->appendEnableServiceConfigFragment($config, $displayTwitter);
		
		if ($displayTwitter) {
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
		$this->openSocialServiceConfigSection($config, "gplus");
		
		$displayGooglePlus = $this->params->get('displayGooglePlus');
		$this->appendEnableServiceConfigFragment($config, $displayGooglePlus);
		
		if ($displayGooglePlus) {
			$googlePermaOption = $this->params->get('googlePermaOption');
			$this->appendEnablePermaOptionFragment($config, $googlePermaOption);
			
			if ($this->isParameterGoogleReferrerTrackNotEmpty()) {
				$googleReferrerTrack = $this->params->get('googleReferrerTrack');
				$this->appendReferrerTrackOptionFragment($config, $googleReferrerTrack);
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
		$config = "\n        'status' : ";
		$this->appendBooleanFlag(&$config, $enableFlag);
	}

	private function appendEnablePermaOptionFragment(&$config, $enableFlag) {
		$config = "\n        'perma_option' : ";
		$this->appendBooleanFlag(&$config, $enableFlag);
	}

	private function appendReferrerTrackOptionFragment(&$config, $referrerTrack) {
		$config  = "\n        'referrer_track' : '";
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

	private function appendCommonConfig(&$config) {
		$config .= "\n      'css_path' : 'socialshareprivacy/socialshareprivacy.css'";
	}

	private function getHeisePluginConfigJavaScript($heisePluginConfig) {
		$javascript  = "    jQuery(document).ready(function($) {\n";
		$javascript .= "      if ($('#socialshareprivacy').length > 0) {\n";
		$javascript .= "        $('#socialshareprivacy').socialSharePrivacy(" . $heisePluginConfig . ");\n";
		$javascript .= "      }\n" ;
		$javascript .= "    });\n";
		
		return $javascript;
	}
}
