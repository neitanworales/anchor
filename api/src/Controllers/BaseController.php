<?php

class BaseController
{
    protected function ok($data = array(), $message = 'ok')
    {
        return array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );
    }

    protected function fail($message, $code = 400, $details = array())
    {
        return array(
            'success' => false,
            'message' => $message,
            'code' => $code,
            'details' => $details,
        );
    }
}
