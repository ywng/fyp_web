function compareSessionDates(dateString)
{
  // Convert the user's text to a Date object
  if(dateString==""){
     return false;
  }
  var userDate = Date.parse(dateString);

  // Get the current time
  var currentDate = new Date();

  if(isNaN(userDate.valueOf()))
  {
    // User entered invalid date
    alert("Invalid date");
    return;
  }

  var difference = -(currentDate - userDate);
  //alert("The date entered differs from today's date by " + difference + " milliseconds");

  if(difference<=1000){//smaller than 1 hour session time 
     //alert("expire");
     return false;
  }

  return true;
}

function checkValidRequestWithSessionToken(dateString)
{
  // Convert the user's text to a Date object
  if(dateString==""){
     return false;
  }
  var userDate = Date.parse(dateString);

  // Get the current time
  var currentDate = new Date();

  if(isNaN(userDate.valueOf()))
  {
    // User entered invalid date
    alert("Invalid date");
    return;
  }

  var difference = -(currentDate - userDate);
  //alert("The date entered differs from today's date by " + difference + " milliseconds");

  if(difference<=1000){//smaller than 1 hour session time 
    //session expired; need to login again to make a valid request (new session token)
     window.location.href="signin.html";
  }

}