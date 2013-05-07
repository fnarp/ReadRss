$(document).ready(function()
{
   $(".feed_edit").click(function()
   {
      var $id = $(this).attr('name');

      $("#" + $id).toggle();
   });
});