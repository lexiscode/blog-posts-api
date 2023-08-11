<?php

namespace  App\Validation;

use App\Requests\CustomRequestHandler;

// This class is from the Respect\Validation library and is used to handle validation exceptions.
use Respect\Validation\Exceptions\NestedValidationException;


class Validator
{

    protected  $requestHandler;
    public $errors = [];

    /**
     * The validate method iterates through the specified rules and applies them to the corresponding 
     * input data retrieved from the HTTP request using the CustomRequestHandler.
     */
    public function validate($request , array $rules)
    {

        foreach ($rules as $field =>$rule)
        {
            try{
                $rule->setName($field)->assert(CustomRequestHandler::getParam($request,$field));
            }catch(NestedValidationException $ex)
            {
                $this->errors[$field] = $ex->getMessages();
            }
        }
        return $this;
    }


    /**
     * The failed method is a simple utility method that returns true if there are any validation errors 
     * in the $errors array, and false otherwise.
     */
    public function failed()
    {
        return !empty($this->errors);
    }

}
