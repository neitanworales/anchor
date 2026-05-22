<?php

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    public $id;
    public $name;
    public $second_name;
    public $last_name;
    public $second_last_name;
    public $user_status;
    public $email;
    public $password_hash;
    public $verification_code;
    public $registered_at;

    public function toArray()
    {
        $data = parent::toArray();
        unset($data['password'], $data['verification_code']);
        $data['name'] = isset($this->name) ? (string) $this->name : '';
        $data['second_name'] = isset($this->second_name) ? (string) $this->second_name : '';
        $data['last_name'] = isset($this->last_name) ? (string) $this->last_name : '';
        $data['second_last_name'] = isset($this->second_last_name) ? (string) $this->second_last_name : '';
        $data['email'] = isset($this->email) ? (string) $this->email : '';
        return $data;
    }
}
