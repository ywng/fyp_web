<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// Please add your custom error here
// format: $config['error'][__number__] = '__Your Error Message__';

$config['error'][0] = 'UNKNOWN ERROR.'; // do not use this except CORE_Controller
$config['error'][1] = 'Failed credential check.';
$config['error'][2] = 'Standard validation error, you should use validation_error() function to generate exact message back to the app.';

// Passenger related error message
$config['error'][3] = 'Phone number is already taken by others.';
$config['error'][4] = 'Email is already taken by others.';
$config['error'][5] = 'Fail to insert user.';
$config['error'][6] = 'Email/Password combo does not exist.';
$config['error'][7] = 'Passenger does not exist.';
$config['error'][8] = 'Nothing to update.';

// Driver related error message


// Trip related error message
$config['error'][101] = 'Order does not exist.';
$config['error'][102] = 'Cannot create order.';
$config['error'][103] = 'Ownership error.';
$config['error'][104] = 'Order is not available for bidding.';

// GPS related error message
$config['error'][1001] = 'Driver does not exist.';
$config['error'][1002] = 'No GPS available.';




// all other error messages
$config['error'][100000001] = 'Not yet implemented.';
$config['error'][100000002] = 'Database error.';


/* End of file error_message.php */
/* Location: ./application/config/error_message.php */