<?php

use Firesphere\Mini\Location;
use SilverStripe\Security\PasswordValidator;
use SilverStripe\Security\Member;
use SilverStripe\View\SSViewer;
use Wilr\GoogleSitemaps\GoogleSitemap;

// remove PasswordValidator for SilverStripe 5.0
$validator = PasswordValidator::create();
// Settings are registered via Injector configuration - see passwords.yml in framework
Member::set_password_validator($validator);
SSViewer::setRewriteHashLinksDefault(false);
GoogleSitemap::register_dataobject(Location::class);
