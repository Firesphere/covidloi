<?php

namespace Firesphere\Mini;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\Validator;

class SearchForm extends Form
{

    public function __construct(
        RequestHandler $controller = null,
        $name = self::DEFAULT_NAME,
        FieldList $fields = null,
        FieldList $actions = null,
        Validator $validator = null
    ) {
        parent::__construct($controller, $name, $fields, $actions, $validator);

        $this->setFormMethod('GET');

        $this->disableSecurityToken();
    }
}
