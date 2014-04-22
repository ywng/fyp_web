function compareDates(dateString)
{
  // Convert the user's text to a Date object
  var userDate = new Date(dateString);

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
}