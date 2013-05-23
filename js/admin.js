(function ($)
{
	"use strict";

	$(".select-all").click(function(e)
	{
	  console.log($("input[type='checkbox']", $(this).data('target') ));
	  e.preventDefault();
	  $("input[type='checkbox']", $(this).data('target') ).attr({'checked':'checked'});
	  return false;
	});
	
	$(".deselect-all").click(function(e)
  {
    e.preventDefault();
    $("input[type='checkbox']", $(this).data('target') ).removeAttr('checked');
    return false;
  });
  
  $(".read-help").click(function(e)
  {
    e.preventDefault();
    $("#contextual-help-link").click();
    return false;
  });
  
}(jQuery));