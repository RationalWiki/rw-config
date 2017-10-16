<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

require __DIR__ . '/PrivateSettings.php';

if ( isset( $_SERVER['HTTP_HOST'] ) ) {
	$host = $_SERVER['HTTP_HOST'];
} elseif ( defined( 'MW_DB' ) ) {
	switch ( MW_DB ) {
	case 'rationalwiki':
		$host = 'rationalwiki.org';
		break;

	case 'ru_rationalwiki':
	case 'staging_rationalwiki':
		$host = str_replace( '_rationalwiki', '.rationalwiki.org', MW_DB );
		break;
	}
} else {
	throw new Exception( 'Unable to determine hostname' );
}

switch ( $host ) {
	case 'rationalwiki.org':
		$wgSitename = "RationalWiki";
		$wgDBname = 'rationalwiki';
		$wgLanguageCode = 'en';
		$wgLocalInterwikis = array( 'RationalWiki', 'en' );
		break;

	case 'staging.rationalwiki.org':
		$wgDBname = 'staging_rationalwiki';
		$wgLanguageCode = 'en';
		$wgLocalInterwikis = array( 'staging' );
		break;

	// case qq.rationalwiki.org
	case 'ru.rationalwiki.org':
		$wgSitename = "РациоВики";
		$wgLanguageCode = str_replace( '.rationalwiki.org', '', $host );
		$wgDBname = "{$wgLanguageCode}_rationalwiki";
		$wgLocalInterwikis = array( $wgLanguageCode );
		break;

	default:
		throw new Exception( 'Invalid host name' . htmlspecialchars( $host ) );
}

$rwSourceBase = realpath( __DIR__ . '/..' );

$wgServer = "//$host";
$wgCanonicalServer = "https://$host";
$wgSecureLogin = true;
$wgCookieDomain = '.rationalwiki.org';

$wgFavicon ="/favicon.ico";
$wgLogo = "/w/images/6/6e/Rw_logo.png";
# and so this is Saturnalia, and what have you done?
# To set the Christmas hat logo, just change File:Rw_logo.png

## The URL base path to the directory containing the wiki;
## defaults for all runtime URL paths are based off of this.
$wgScriptPath = "/w";
$wgScript = "$wgScriptPath/index.php";
$wgRedirectScript = "$wgScriptPath/redirect.php";
$wgUsePathInfo = true;
$wgArticlePath = "/wiki/$1";

$wgEnableEmail      = true;
$wgEnableWriteAPI = true;
$wgEnableUserEmail  = true;
$wgAllowUserJs = true;
$wgAllowUserCss = true;

### Blocks and bans
# Allow blocked users to edit their user talk page
$wgBlockAllowsUTEdit = true;
# Range blocks are a no-no
$wgBlockCIDRLimit = array (
       'IPv4' => 16,
       'IPv6' => 19, # 2^109 = ~6.5x10^32 addresses
);
# IP autobans will expire
$wgAutoblockExpiry = '31415 seconds'; #9 hours
# Blacklisting IP proxies
$wgEnableDnsBlacklist = true;
$wgDnsBlacklistUrls = array( 'xbl.spamhaus.org', 'dnsbl.tornevall.org', 'all.s5h.net' );

### AbuseFilter
## AbuseFilter settings
# Poorly documented, appears to deactivate AntiSpoof and enable logging mode https://github.com/wikimedia/mediawiki-extensions-AntiSpoof/blob/master/AntiSpoofHooks.php
$wgAntiSpoofAccounts = false;
# Shows filter performance stats
$wgAbuseFilterProfile = true;
# AbuseFilter block settings (note: no filters have block enabled)
$wgAbuseFilterBlockDuration = '314159 seconds';
## AbuseFilter user rights
# Everyone can view (non-hidden) AbuseFilters and AbuseFilter logs
$wgGroupPermissions['*']['abusefilter-view'] = true;
$wgGroupPermissions['*']['abusefilter-log'] = true;
$wgGroupPermissions['*']['abusefilter-log-detail'] = true;
# Techs can edit AbuseFilters
$wgGroupPermissions['tech']['abusefilter-modify'] = true;
$wgGroupPermissions['tech']['abusefilter-modify-restricted'] = true;
# Techs can view private data from AbuseFilter logs
$wgGroupPermissions['tech']['abusefilter-private'] = true;
# Techs can revert all actions of an AbuseFilter
$wgGroupPermissions['tech']['abusefilter-revert'] = true;

$wgEmergencyContact = "rationalwiki@rationalwiki.org";
$wgPasswordSender = "rationalwiki@rationalwiki.org";

## For a detailed description of the following switches see
## http://meta.wikimedia.org/Enotif and http://meta.wikimedia.org/Eauthent
## There are many more options for fine tuning available see
## /includes/DefaultSettings.php
## UPO means: this is also a user preference option
$wgEnotifUserTalk = true; # UPO
$wgEnotifWatchlist = true; # UPO
$wgEmailAuthentication = true;

$wgDBtype           = "mysql";
$wgDBserver         = "localhost";
$wgDBuser           = "rw_web";
$wgDBprefix         = "";

# MySQL table options to use during installation or update
$wgDBTableOptions   = "TYPE=InnoDB";

# Shared tables
if ( $wgDBname !== 'rationalwiki' ) {
	$wgSharedDB = 'rationalwiki';
	$wgSharedTables[] = 'user_groups';
	$wgSharedTables[] = 'ipblocks';
	$wgSharedTables[] = 'vandals';
	$wgSharedTables[] = 'updates';
	$wgSharedTables[] = 'intercom_list';
	$wgSharedTables[] = 'intercom_message';
	$wgSharedTables[] = 'intercom_read';
	$wgSharedTables[] = 'user_message_state';
	$wgSharedTables[] = 'abuse_filter_action';
	$wgSharedTables[] = 'abuse_filter';
	$wgSharedTables[] = 'wigotext';	
}

# Uploads
$wgUploadPath = '/w/images';
$wgUploadDirectory = "/bulk/images/{$host}";

if ( $wgDBname === 'rationalwiki' || $wgDBname === 'staging_rationalwiki' ) {
	$wgEnableUploads = true;
} else {
	$wgEnableUploads = false;
	$wgUploadNavigationUrl = "https://rationalwiki.org/wiki/Special:Upload";

	$wgForeignFileRepos[] = array(
		'class' => 'ForeignDBViaLBRepo',
		'name' => 'shared',
		'directory' => '/bulk/images/rationalwiki.org',
		'url' => 'https://rationalwiki.org/w/images',
		'wiki' => 'rationalwiki',
		'hasSharedCache' => true,
	);
}

$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgUseTeX = true;
$wgFileExtensions[] = 'svg';
$wgFileExtensions[] = 'xcf';
$wgSVGConverter='rsvg';
$wgFileExtensions[] = 'mp3';
$wgFileExtensions[] = 'pdf';
$wgFileExtensions[] = 'djvu';
$wgFileExtensions[] = 'ttf';
$wgFileExtensions[] = 'eot';
$wgFileExtensions[] = 'woff';


## Default skin: you can change the default skin. Use the internal symbolic
## names, ie 'standard', 'nostalgia', 'cologneblue', 'monobook':
$wgDefaultSkin = 'vector';

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
$wgEnableCreativeCommonsRdf = true;
$wgRightsPage = "RationalWiki:Copyrights"; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "https://creativecommons.org/licenses/by-sa/3.0/";
$wgRightsText = "CC-BY-SA 3.0, or any later version";
#$wgRightsIcon = "http://i.creativecommons.org/l/by-sa/3.0/88x31.png";
#improve performance, saves a dns query, png is optimized to save a few kilobytes
$wgRightsIcon = "/w/88x31.png";
# $wgRightsCode = "gfdl"; # Not yet used

$wgAllowDisplayTitle = true;

$wgDiff = "/usr/bin/diff";
$wgDiff3 = "/usr/bin/diff3";

## Caching

$wgMainCacheType = CACHE_MEMCACHED;
$wgParserCacheType = CACHE_MEMCACHED;
$wgMessageCacheType = CACHE_MEMCACHED;
$wgMemCachedServers = array( "127.0.0.1:11211" );

$wgCacheDirectory = "/bulk/cache/{$wgDBname}";

# Update this timestamp to clear the parser cache
$wgCacheEpoch = '20170607000000';

#$wgReadOnly = "Update in progress see tech blog.";

### Namespaces
define("NS_CONSERVAPEDIA", 100);
define("NS_CONSERVAPEDIA_TALK", 101);
define("NS_ESSAY", 102);
define("NS_ESSAY_TALK", 103);
define("NS_DEBATE", 104);
define("NS_DEBATE_TALK", 105);
define("NS_FUN", 106);
define("NS_FUN_TALK", 107);
define("NS_RECIPE", 108);
define("NS_RECIPE_TALK", 109);
define("NS_FORUM", 110);
define("NS_FORUM_TALK", 111);

if ( $wgDBname === 'rationalwiki' ) {
	$wgExtraNamespaces = array(
		NS_CONSERVAPEDIA => "Conservapedia",
		NS_CONSERVAPEDIA_TALK => "Conservapedia_talk",
		NS_ESSAY => "Essay",
		NS_ESSAY_TALK => "Essay_talk",
		NS_DEBATE => "Debate",
		NS_DEBATE_TALK => "Debate_talk",
		NS_FUN => "Fun",
		NS_FUN_TALK => "Fun_talk",
		NS_RECIPE => "Recipe",	
		NS_RECIPE_TALK => "Recipe_talk",	
		NS_FORUM => "Forum",
		NS_FORUM_TALK => "Forum_talk"
	);

	$wgNamespacesWithSubpages = array(
		NS_MAIN                  => false,
		NS_TALK                  => true,
		NS_USER                  => true, 
		NS_USER_TALK             => true,
		NS_PROJECT               => true, 
		NS_PROJECT_TALK          => true, 
		NS_IMAGE_TALK            => true, 
		NS_TEMPLATE              => true,
		NS_TEMPLATE_TALK         => true, 
		NS_HELP                  => true,
		NS_HELP_TALK             => true, 
		NS_CATEGORY_TALK         => true, 
		NS_CONSERVAPEDIA         => true,
		NS_CONSERVAPEDIA_TALK    => true,
		NS_ESSAY                 => true,
		NS_ESSAY_TALK            => true,
		NS_DEBATE                => true,
		NS_DEBATE_TALK           => true,
		NS_FUN                   => true,
		NS_FUN_TALK              => true,
		NS_RECIPE                => true,
		NS_RECIPE_TALK           => true,
		NS_FORUM                 => true,
		NS_FORUM_TALK            => true
	);
}

$wgNamespaceAliases = array(
        'RW' => NS_PROJECT,
        'RW_talk' => NS_PROJECT_TALK
    );

$wgNamespacesToBeSearchedDefault = array( 
       NS_MAIN              => true, 
);

$wgNoFollowLinks = true;

$wgCleanSignatures = false;
$wgRestrictDisplayTitle = false;

# Don't run jobs from the web, there's /etc/cron.d/mw-job-runner for that
$wgJobRunRate = 0;

$wgMaxNameChars = 255;

$wgAutoConfirmAge = 3600*24;
$wgAutoConfirmCount = 10;

## More password attempts, less annoyance
$wgPasswordAttemptThrottle = array( 'count' => 10, 'seconds' => 300 );

## add Commons as file repo
$wgUseInstantCommons = false;
$wgForeignFileRepos[] = array(
	'class' => 'ForeignAPIRepo',
	'name' => 'wikimediacommons',
	'apibase' => 'https://commons.wikimedia.org/w/api.php',
	'url' => 'https://upload.wikimedia.org/wikipedia/commons',
	'thumbUrl' => 'https://upload.wikimedia.org/wikipedia/commons/thumb',
	'hashLevels' => 2,
	'transformVia404' => true,
	'fetchDescription' => true,
	'descriptionCacheExpiry' => 43200,
	'apiThumbCacheExpiry' => 86400,
);

## User rights
## These must be the same on all wikis, since we use a shared user_groups table
#$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['autoconfirmed']['rollback'] = true;
$wgGroupPermissions['autoconfirmed']['move'] = true;
## Vandals were uploading porn, so only autoconfirmed users can upload
$wgGroupPermissions['user']['upload'] = false;
$wgGroupPermissions['autoconfirmed']['upload'] = true;
$wgGroupPermissions['autoconfirmed']['upload_by_url'] = true;
## delete and suppress (hide from sysops) revisions
$wgGroupPermissions['sysop']['deleterevision']  = true;
$wgGroupPermissions['sysop']['deletelogentry'] = true;
$wgGroupPermissions['moderator']['suppressrevision'] = true;
$wgGroupPermissions['moderator']['suppressionlog'] = true;
$wgGroupPermissions['tech']['suppressrevision'] = true;
$wgGroupPermissions['tech']['suppressionlog'] = true;
## everyone can see the suppression log, though it's not on RecentChanges
$wgGroupPermissions['*']['suppressionlog'] = true;
$wgGroupPermissions['user' ]['move'] = false;
## Vandal bin uses the noratelimit right for 15 second limit, may break automated bot scripts
$wgGroupPermissions['bot']['noratelimit'] = true;
$wgGroupPermissions['bot']['editprotected'] = false;
$wgGroupPermissions['tech']['intercom-sendurgent'] = true;
$wgGroupPermissions['moderator']['intercom-sendurgent'] = true;
$wgGroupPermissions['ninja']['edit'] = true;
## prevent sysops from accessing dangerous stuff (Oct 27 2009)
$wgGroupPermissions['sysop']['editinterface'] = false;
$wgGroupPermissions['sysop']['editusercss'] = false;
$wgGroupPermissions['sysop']['edituserjs'] = false;
$wgGroupPermissions['sysop']['bigdelete'] = false;
$wgGroupPermissions['sysop']['import'] = false;
$wgGroupPermissions['sysop']['importupload'] = false;
$wgDeleteRevisionsLimit = 2000;
$wgGroupPermissions['sysop']['blockemail'] = false;
$wgGroupPermissions['moderator']['blockemail'] = true;

## give the above to techs
$wgGroupPermissions['staff']['editinterface'] = true;
$wgGroupPermissions['tech']['editinterface'] = true;
$wgGroupPermissions['tech']['editusercss'] = true;
$wgGroupPermissions['tech']['edituserjs'] = true;
$wgGroupPermissions['tech']['bigdelete'] = true;
$wgGroupPermissions['tech']['import'] = true;
$wgGroupPermissions['tech']['importupload'] = true;

## techs that are not sysops
$wgGroupPermissions['tech']['autopatrol'] = true;
$wgGroupPermissions['tech']['patrol'] = true;
## allow moving without creating redirect
$wgGroupPermissions['sysop']['suppressredirect'] = true;

## revoking sysops rights without edit waring
$wgGroupPermissions['sysoprevoke']['block']  = false;
$wgRevokePermissions['sysoprevoke']['block'] = true;
$wgRevokePermissions['sysoprevoke']['delete'] = true;
$wgRevokePermissions['sysoprevoke']['protect'] = true;
$wgRevokePermissions['sysoprevoke']['userrights'] = true;

##moderator-level protection, uncomment to enable
##Also uncomment the line in setupCustomNames
$wgRestrictionLevels[] = 'moderator';

##moderators and techs can grant and revoke all user rights
$wgGroupPermissions['moderator']['moderator'] = true;
$wgGroupPermissions['tech']['moderator'] = true;

## disable 15 second timeout for autoconfirmed, since not everyone wants to be a sysop (Sept 29 2010)
$wgGroupPermissions['autoconfirmed']['noratelimit'] = true;

## Allow trusted users to bot themselves for repetitive edits without needing to go through RfB
$wgGroupsAddToSelf['ninja'] = array('bot');
$wgGroupsRemoveFromSelf['ninja'] = array('bot');
$wgGroupPermissions['ninja']['import'] = true;
$wgGroupPermissions['ninja']['importupload'] = true;

$wgAddGroups['sysop'] = array('sysop', 'autopatrolled');
$wgRemoveGroups['sysop'] = array('sysop', 'autopatrolled');

$wgAddGroups['tech'] = true;
$wgRemoveGroups['tech'] = true;

$wgGroupPermissions['moderator']['userrights'] = true;

#autopatrolled users
$wgGroupPermissions['autopatrolled']['noratelimit'] = true;
$wgGroupPermissions['autopatrolled']['autopatrol'] = true;
$wgGroupPermissions['autopatrolled']['skipcaptcha'] = true;
$wgGroupPermissions['autopatrolled']['upload'] = true;

#techs get gadget rights
$wgGroupPermissions['tech']['gadgets-edit'] = true;
$wgGroupPermissions['tech']['gadgets-definition-edit'] = true;
$wgGroupPermissions['tech']['gadgets-definition-create'] = true;
$wgGroupPermissions['tech']['gadgets-definition-delete'] = true;

## Allow uploading from url, requires php5-curl
$wgAllowCopyUploads = true;

## to make licenses subpages work
$wgForceUIMsgAsContentMsg = array( 'licenses' );
$wgExtraLanguageNames = array( 'en-ownwork' => 'English upload form for own work',
                               'en-fairuse' => 'English upload form for fair use',
                               'en-free' => 'English upload form for free media', );

##
##  Extensions
##

$wgExtensionDirectory = "$rwSourceBase/extensions";
$wgStyleDirectory = "$rwSourceBase/skins";

wfLoadExtensions( array(
	'AbuseFilter',
	'AntiSpoof',
	'CharInsert',
	'Cite',
	'ConfirmEdit',
	'ConfirmEdit/ReCaptchaNoCaptcha',
	'Elastica',
	'EmbedVideo',
	'Gadgets',
	'ImageFilter',
	'ImageMap',
	'InputBox',
	'Interwiki',
	'Math',
	'ParserFunctions',
	'PdfHandler',
	'RationalWiki',
	'Renameuser',
	'SyntaxHighlight_GeSHi',
	'WikiEditor',
) );

wfLoadSkins( array(
	'CologneBlue',
	'Modern',
	'MonoBook',
	'Nostalgia',
	'Vector'
) );

### Vandal brake and vandal bin
require_once("$wgExtensionDirectory/VandalBrake2/VandalBrake2.php");
$wgVandalBrakeConfigAllowMove = false;
$wgVandalBrakeConfigRemoveRights[] = 'intercom-sendmessage';
$wgVandalBrakeConfigRemoveRights[] = 'upload';

## Math
$wgMathValidModes = [ 'source', 'png' ];

## Recaptcha
$wgCaptchaClass = 'ReCaptchaNoCaptcha';

## captcha switch for logins, set to false when bot can't log in
$wgCaptchaTriggers['edit'] = true;
$wgCaptchaTriggers['badlogin']      = false;

##Template edits are costly, prevent spambots from editing them
$wgCaptchaTriggersOnNamespace[NS_TEMPLATE]['edit'] = true;
$wgNamespaceProtection[NS_TEMPLATE] = array( 'autoconfirmed' );
##Forum and Forum talk namespaces get more spam because of their name
$wgCaptchaTriggersOnNamespace[NS_FORUM]['edit'] = true;
$wgCaptchaTriggersOnNamespace[NS_FORUM_TALK]['edit'] = true;

$wgGroupPermissions['autoconfirmed']['skipcaptcha'] = true;

## Paypal buttons
require_once("$wgExtensionDirectory/RationalWiki/paypal.php");

## Wigo and other polls

require_once("$wgExtensionDirectory/Wigo3/wigo3.php");
$wgWigo3ConfigStoreIPs = true;
require_once("$wgExtensionDirectory/Wigo3/slider.php");
require_once("$wgExtensionDirectory/Wigo3/checkbox.php");
require_once("$wgExtensionDirectory/Wigo3/multi.php");
require_once("$wgExtensionDirectory/bestof/bestof.php");
require_once( "$wgExtensionDirectory/AutoWIGO2/AutoWIGO2.php" );
require_once( "$wgExtensionDirectory/RWElection/RWElection.php" );
#$wgElectionName = "Board2017";
#$wgElectionCandidates = array("Spud", "Human", "Ikanreed", "Colonel Sanders");
#$wgElectionStoreDir = "$IP/../election";

## Intercom
# Uses sajax_do_call() which was removed in 1.26
# require_once("$wgExtensionDirectory/Intercom/Intercom.php");

## Put nofollow even on interwiki links, because we don't want to increase CP's page rankings
require_once("$wgExtensionDirectory/iw-nofollow/iw-nofollow.php");

## Renameuser
$wgGroupPermissions['moderator']['renameuser'] = true;
$wgGroupPermissions['tech']['renameuser'] = true;
 
## [[Special:Editcount]]
require_once("$wgExtensionDirectory/RWEditcount/RWEditcount.php");
 
## Bible tag for quick and easy Bible-thumping
require_once("$wgExtensionDirectory/RationalWiki/bible.php");

# ParserFunctions
$wgPFEnableStringFunctions = true;

require_once("$wgExtensionDirectory/DynamicPageList/DynamicPageList.php");
require_once("$wgExtensionDirectory/SubPageList/SubPageList.php");
require_once("$wgExtensionDirectory/Variables/Variables.php");
require_once("$wgExtensionDirectory/RandomSelection/RandomSelection.php");
## expand parserfunction, subst:expand fully expands templates
require_once("$wgExtensionDirectory/RationalWiki/Expand.php");
require_once("$wgExtensionDirectory/DynamicFunctions/DynamicFunctions.php");
require_once("$wgExtensionDirectory/ImageMap/ImageMap.php");
require_once("$wgExtensionDirectory/Echo/Echo.php");

# Interwiki
$wgGroupPermissions['tech']['interwiki'] = true;

## Ogg support
require( "$wgExtensionDirectory/OggHandler/OggHandler.php" );
$wgFFmpegLocation = '/usr/bin/ffmpeg';

## PDF and DjVu support
$wgDjvuDump = "djvudump";
$wgDjvuRenderer = "ddjvu";

## CirrusSearch
require_once( "$wgExtensionDirectory/CirrusSearch/CirrusSearch.php" );
$wgSearchType = 'CirrusSearch';

## extension to hide the page title
require_once("$wgExtensionDirectory/RationalWiki/notitle.php");
## extension to change the page title style
require_once("$wgExtensionDirectory/RationalWiki/styletitle.php");
## checks if a new comment is signed
require_once("$wgExtensionDirectory/SigChecker/SigChecker.php");

$wgVectorUseSimpleSearch = true;
$wgVectorUseIconWatch = true;

# WikiEditor (Toolbar, Toc, Preview, Highlight)
$wgWikiEditorModules = array(
        'highlight' => array( 'global' => false, 'user' => false ),
        'preview' => array( 'global' => false, 'user' => false ),
        'toc' => array( 'global' => false, 'user' => true ),
        'toolbar' => array( 'global' => false, 'user' => true ),
);

$wgDefaultUserOptions['usebetatoolbar'] = 1;
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;

require_once( "$wgExtensionDirectory/LiquidThreads/LiquidThreads.php" );
$wgLqtTalkPages = false;

$wgMaxShellMemory = 0;
$wgThumbnailEpoch = '20101114210500';
$wgLocaltimezone = "UTC";

$logGroups = array(
	'CirrusSearch',
	'exception',
	'lqt',
	'slow-parse'
);
foreach ( $logGroups as $logGroup ) {
	$wgDebugLogGroups[$logGroup] = "/var/log/mw/$logGroup.log";
}
$wgDBerrorLog = '/var/log/mw/dberror.log';

$wgNamespaceRobotPolicies = array( NS_USER => 'noindex', NS_USER_TALK => 'noindex' );

$wgShowExceptionDetails = true;
$wgUseSquid = true;
$wgSquidServers = array('45.33.90.21', '127.0.0.1');
$wgDisableCounters = true;

$wgShellLocale = "en_US.utf8";

# Enable caching of DynamicPageList, otherwise the Varnish cache is suppressed for virtually every main namespace page
ExtDynamicPageList::$respectParserCache = true;

# Less annoying watchlist notifications
$wgDefaultUserOptions['watchcreations'] = 1;
$wgDefaultUserOptions['watchdefault'] = 0;
$wgDefaultUserOptions['enotifwatchlistpages'] = 0;

foreach ( rwPrivateSettings() as $name => $value ) {
	$GLOBALS[$name] = $value;
}
