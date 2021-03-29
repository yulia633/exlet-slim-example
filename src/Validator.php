<?php

namespace App;

class Validator
{
    public function validate(array $user)
    {
        $errors = [];
        foreach ($user as $key => $value) {
            if (empty($user[$key])) {
                $errors[$key] = "{$key} cant` be blank";
            }
        }
        return $errors;
    }
}
