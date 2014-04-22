function compareDates(dateString)
{
  // Convert the user's text to a Date object
  var userDate = Date.parse(dateString);

  // Get the current time
  var currentDate = new Date();

  if(isNaN(userDate.valueOf()))
  {
    // User entered invalid date
    alert("Invalid date");
    return;
  }

  var difference = currentDate - userDate;
  alert("The date entered differs from today's date by " + difference + " milliseconds");

  if(difference>24*60*60*1000000){
     alert("expire");
     return false;
  }
  
  return true;
}