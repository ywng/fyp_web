<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// Please add your custom error here
// format: $config['error'][__number__] = '__Your Error Message__';

$config['error'][0] = 'UNKNOWN ERROR.'; // do not use this except CORE_Controller
$config['error'][1] = 'Failed credential check';
$config['error'][2] = 'Standard validation error, you should use validation_error() function to generate exact message back to the app.';

// User related error message
$config['error'][3] = 'Username is already taken by others.';
$config['error'][4] = 'Phone number is already taken by others.';
$config['error'][5] = 'Email is already taken by others.';
$config['error'][6] = 'Fail to insert user';

// Driver related error message


// Trip related error message


/* End of file error_message.php */
/* Location: ./application/config/error_message.php */