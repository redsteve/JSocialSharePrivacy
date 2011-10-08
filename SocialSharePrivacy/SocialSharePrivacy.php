<?php
/**
 * Social Share Privacy PlugIn for Joomla! 1.7 or higher
 * @link      https://github.com/redsteve/JSocialSharePrivacy
 * @copyright Copyright (C) 2011 Stephan Roth. All rights reserved.
 * @license   MIT License; see MIT-LICENSE file
 */

defined('_JEXEC') or die('Restricted access!');

jimport('joomla.plugin.plugin');

define('SOCIAL_SHARE_PRIVACY_BASE_URL', JURI::base() . "plugins/content/SocialSharePrivacy/");

final class plgContentSocialSharePrivacy extends JPlugin
{
  const divElementToSubstitute = "\n<div id=\"socialshareprivacy\"></div>\n";
  const socialSharePrivacyJScript = "jquery.socialshareprivacy.js";
  const socialSharePrivacyMinimizedJScript = "jquery.socialshareprivacy.min.js";

  public function __construct(&$subject, $config)
  {
    parent::__construct($subject, $config);
    $this->loadLanguage();
  }

	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
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
    
		switch ($position)
    {
			case 1 :
        $article->text = self::divElementToSubstitute . $article->text;
        break;
          
      case 2 :
        $article->text = $article->text . self::divElementToSubstitute;
        break;
    }
		
		return true;
	}
	
	private function isAdministrationArea(&$application)
	{
	  return ($application->isAdmin());
  }
  
  private function isHtmlDocument(&$document)
  {
    $documentType = $document->getType();
    return (strcmp("html", $documentType) == 0);
  }
  
  private function isArticle()
  {
    $view = JRequest::getString('view', '');
    return ($view == 'article');
  }
  
  private function isParameterJQueryUrlNotEmpty()
  {
    $jQueryUrl = $this->params->get('jqueryurl');
    return (! (empty($jQueryUrl)));
  }
  
	private function addCustomTagsToHeader(&$document)
	{
	  if ($this->isParameterJQueryUrlNotEmpty())
	  {
	    $this->addJQueryUrlToHeader($document);
    }
    $this->addHeiseSocialSharePrivacyJScriptToHeader($document);
    $this->addOptionsJScriptToHeader($document);
  }
  
  private function addJQueryUrlToHeader(&$document)
  {
    $jQueryUrl = $this->params->get('jqueryurl');
    $document->addScript($jQueryUrl);
  }
  
  private function addHeiseSocialSharePrivacyJScriptToHeader(&$document)
  {
    $heisePluginUrl = SOCIAL_SHARE_PRIVACY_BASE_URL . 'plugin/';
    $useCompressedScript = $this->params->get('compressed');
    
    if ($useCompressedScript == 1)
    {
      $document->addScript($heisePluginUrl . self::socialSharePrivacyMinimizedJScript);
    } else {
      $document->addScript($heisePluginUrl . self::socialSharePrivacyJScript);
    }
  }
  
  private function addOptionsJScriptToHeader(&$document)
  {
    $heisePluginConfig = $this->getHeisePluginConfig();
    $javascript = $this->getHeisePluginConfigJavaScript($heisePluginConfig);
    /** $document->addScriptDeclaration($javascript); */
  }
  
  private function getHeisePluginConfig()
  {
    $config = "\n    services : {";

    $this->appendFacebookConfig($config);
    $this->appendTwitterConfig($config);
    $this->appendGooglePlusConfig($config);
    
    $config .= "\n    },";
    
    $this->appendCommonConfig($config);
    
    return $config;
  }
  
  private function appendFacebookConfig(&$config)
  {
    $config .= "\n      facebook : {";
    
    $displayFacebook = $this->params->get('displayFacebook');
    $this->appendEnableConfigFragment($config, $displayFacebook);
    if ($displayFacebook)
    {
    
    }
    
    $config .= "\n      },";
  }
  
  private function appendTwitterConfig(&$config)
  {
    $config .= "\n      twitter : {";
    
    $displayTwitter = $this->params->get('displayTwitter');
    $this->appendEnableConfigFragment($config, $displayTwitter);
    if ($displayTwitter)
    {
    
    }
    
    $config .= "\n      },";
  }
  
  private function appendGooglePlusConfig(&$config)
  {
    $config .= "\n      gplus : {";
    
    $displayGooglePlus = $this->params->get('displayGooglePlus');
    $this->appendEnableConfigFragment($config, $displayGooglePlus);
    if ($displayGooglePlus)
    {
    
    }
    
    $config .= "\n      },";
  }
  
  private function appendEnableConfigFragment(&$config, $enableFlag)
  {
    if ($enableFlag)
    {
      $config .= "\n        'status' : 'on',";
    } else {
      $config .= "\n        'status' : 'off',";
    }
  }
  
  private function appendCommonConfig(&$config)
  {
    /** $config .= "\n      'css_path' : 'socialshareprivacy/socialshareprivacy.css'"; */
  }

  private function getHeisePluginConfigJavaScript($heisePluginConfig)
  {
    $javascript = "    jQuery(document).ready(function($) {\n";
    $javascript .= "      if ($('#socialshareprivacy').length > 0) {\n";
    $javascript .= "        $('#socialshareprivacy').socialSharePrivacy(" . $heisePluginConfig . ");\n";
    $javascript .= "      }\n" ;
    $javascript .= "    });\n";
    
    return $javascript;
  }
}
